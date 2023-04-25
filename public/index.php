<?php

class Template extends ArrayObject {
    protected $templateName;

    public function __construct($name, array $input = array()) {
        $this->templateName = $name;
        $input['imgUrl'] = 'https://i.' . $_SERVER['HTTP_HOST'];
        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function offsetGet(mixed $key): mixed {
        return $this->offsetExists($key) ? parent::offsetGet($key) : null;
    }

    public function __toString() {
         ob_start();
         include '../views/' . $this->templateName . '.php';
         return ob_get_clean();
    }
}

function env($key, $default = null) {
    static $env;
    if (!$env) { $env = require_once '../config/env.php'; }
    return (isset($env[$key]) ? $env[$key] : $default);
}

function getRequestUrl() {
    return sprintf(
        'http%s://%s%s',
        ($_SERVER['HTTPS'] == 'on' ? 's' : ''),
        $_SERVER['HTTP_HOST'],
        $_SERVER['REQUEST_URI']
    );
}

function pay($name) {
    $uri = $_SERVER['REQUEST_URI'];
    $amount = preg_replace('~^/pay/(\d+(?:\.\d\d)?)$~', '$1', $uri);
    $vars = array('monzo'  => '/monzo', 'paypal' => '/paypal');

    if ($amount != $uri && is_numeric($amount) && $amount > 0 && $amount < 10000) {
        foreach ($vars as &$var) {
            $var .= "/$amount";
        }

        if (class_exists('NumberFormatter')) {
            $fmt = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
            $vars['amount'] = str_replace('.00', '', $fmt->formatCurrency((float) $amount, 'GBP'));
        }
    }

    $vars['who'] = $name;
    return array('body' => new Template('pay', $vars));
}

function gravatar() {
    $uri = $_SERVER['REQUEST_URI'];
    $email = preg_replace('~^/grav/(.+)$~', '$1', $uri);
    return 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($email))) . '?d=mm';
}

function qsa($url) {
    return $url . (strpos($url, '?') ? '&' : '?') . $_SERVER['QUERY_STRING'];
}

function map($address, $title, array $img) {
    $userAgent = $_SERVER['HTTP_USER_AGENT'];
    $url = $body = null;
    $query = array('q' => "$address ($title)");
    $querySimple = array('q' => $address);
    $queryString = http_build_query($query);
    $queryStringSimple = http_build_query($querySimple);
    $requestUrl = getRequestUrl();

    switch (true) {
        // chrome on iOS doesn't like Apple's documented approach
        case strpos($userAgent, 'iPhone') && strpos($userAgent, 'CriOS'):
            $url = 'maps:';
            break;

        // Facebook app likes maps: links but shows a blank page in its browser on return
        case isset($_GET['testmap']):
        case strpos($userAgent, 'iPhone') && strpos($userAgent, 'FBAN/FBIOS'):
        case false !== strpos($userAgent, 'facebookexternalhit'):
        case false !== strpos($userAgent, 'Slackbot-LinkExpanding'):
            $body = new Template('map', array(
                'title'             => $title,
                'desc'              => 'A map of ' . preg_replace('/\s*\(.+?\)$/', '', $query['q']) . '.',
                'requestUrl'        => preg_replace('/[?&]fbclid=[^&]*/', '', $requestUrl),
                'lat'               => $img[0],
                'lng'               => $img[1],
                'heading'           => $img[2],
                'zoom'              => $img[3],
                'queryString'       => $queryString,
                'queryStringSimple' => $queryStringSimple,
                'address'           => $address,
                'key'               => env('google-api-key'),
            ));

            // @todo sort out maps links for Android
            break;

        // iOS Safari sometimes prompts if links to maps: are typed in the address bar
        case strpos($userAgent, 'iPhone'):
            $url = 'http://maps.apple.com/maps?';
            break;

        // @todo WP7/Android/BB http://bit.ly/UMUUkt

        default:
            $url = 'https://maps.google.com/maps?';
    }

    return array(
        'body' => $body,
        'url'  => (null === $url ? null : $url . $queryString),
    );
}

function runPlugin(array $config) {
    $params = (isset($config['params']) ? $config['params'] : []);
    if (!is_array($params)) { $params = [$params]; }

    try {
        return call_user_func_array($config['plugin'], $params);
    } catch (TypeError $e) {
        error_log("Invalid smurl plugin [{$config['plugin']}]");
        http_response_code(501);
    }
}

$linksFile = '../config/links.yml';
$links = yaml_parse(file_get_contents($linksFile));

$allPatterns = '#' . implode('|', array_column($links['patterns'], 'pattern')) . '#';

$host = $_SERVER['HTTP_HOST'];
$path = ltrim($_SERVER['PATH_INFO'], '/');

if (isset($links['static'][$path])) {
    $location = $links['static'][$path];

    if (isset($location['plugin'])) {
        $location = runPlugin($location);
    }

    if (is_array($location)) {
        if (isset($location['body'])) {
            if (isset($location['type'])) {
                header('Content-Type: ' . $location['type']);
            }

            echo $location['body'];
        } elseif (isset($location['url'])) {
            header('Location: ' . $location['url']);
        }
    } else {
        if ($location[0] == '/') {
            $location = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . ':' . $location;
        }
        header('Location: ' . $location);
    }
} elseif (preg_match($allPatterns, $path)) {
    foreach ($links['patterns'] as $link) {
        $pattern = $link['pattern'];
        $target = $link['target'];

        if (is_array($target) && preg_match("#$pattern#", $path)) {
            if (isset($target['plugin'])) {
                $target = runPlugin($target);
            }

            if (isset($target['body'])) {
                echo $target['body'];
            }

            if (is_string($target)) {
                header('Location: ' . $target);
            }

            return;
        } elseif (is_string($target)) {
            $newPath = preg_replace("#$pattern#", $target, $path, -1, $count);

            if ($count) {
                header('Location: ' . $newPath);
                return;
            }
        }
    }

    http_response_code(404);
} else {
    if ($glob = glob("../img/c/$path*")) {
        $file = $glob[0];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $type = finfo_file($finfo, $file);
        finfo_close($finfo);
        $ua = $_SERVER['HTTP_USER_AGENT'];

        if (false !== strpos($type, 'video/') && strpos($ua, 'Safari/') && !strpos($ua, 'Chrome/')) {
            header('Location: https://i.' . $_SERVER['HTTP_HOST'] . '/c/' . basename($file));
            return;
        }

        header('Content-Type: ' . $type);
        readfile($file);
    } else {
        http_response_code(404);
    }
}
