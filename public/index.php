<?php

class Template extends ArrayObject {
    protected $_templateName;

    public function __construct($name, array $input = array()) {
        $this->_templateName = $name;
        $input['imgUrl'] = 'https://i.' . $_SERVER['HTTP_HOST'];
        parent::__construct($input, self::ARRAY_AS_PROPS);
    }

    public function __toString() {
         ob_start();
         include '../views/' . $this->_templateName . '.php';
         return ob_get_clean();
    }
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
    $vars = array('who' => $name, 'monzo'  => '/monzo', 'paypal' => '/paypal');

    if ($amount != $uri && is_numeric($amount) && $amount > 0 && $amount < 10000) {
        foreach ($vars as &$var) {
            $var .= "/$amount";
        }

        $fmt = new NumberFormatter('en_GB', NumberFormatter::CURRENCY);
        $vars['amount'] = str_replace('.00', '', $fmt->formatCurrency((float) $amount, 'GBP'));
    }

    return array('body' => new Template('pay', $vars));
}

function qsa($url) {
    return $url . (strpos($url, '?') ? '&' : '?') . $_SERVER['QUERY_STRING'];
}

function map($address, $title, array $img) {
    global $env;
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
                'requestUrl'        => $requestUrl,
                'lat'               => $img[0],
                'lng'               => $img[1],
                'heading'           => $img[2],
                'zoom'              => $img[3],
                'queryString'       => $queryString,
                'queryStringSimple' => $queryStringSimple,
                'address'           => $address,
                'key'               => $env['google-api-key'],
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

$env = require_once '../env.php';
$paths = array(
);

$patterns = array(
);

$allPatterns = '#' . implode('|', array_keys($patterns)) . '#';

$host = $_SERVER['HTTP_HOST'];
$path = ltrim($_SERVER['PATH_INFO'], '/');

if (isset($paths[$path])) {
    if (is_array($paths[$path])) {
        if (isset($paths[$path]['body'])) {
            if (isset($paths[$path]['type'])) {
                header('Content-Type: ' . $paths[$path]['type']);
            }
            echo $paths[$path]['body'];
        } elseif (isset($paths[$path]['url'])) {
            header('Location: ' . $paths[$path]['url']);
        }
    } else {
        $location = $paths[$path];
        if ($location[0] == '/') {
            $location = 'http' . ($_SERVER['HTTPS'] == 'on' ? 's' : '') . ':' . $location;
        }
        header('Location: ' . $location);
    }
} elseif (preg_match($allPatterns, $path)) {
    foreach ($patterns as $pattern => $replacement) {
        if (is_array($replacement) && preg_match("#$pattern#", $path)) {
            if (isset($replacement['body'])) {
                echo $replacement['body'];
            }
            return;
        } else {
            $newPath = preg_replace("#$pattern#", $replacement, $path, -1, $count);

            if ($count) {
                header('Location: ' . $newPath);
                return;
            }
        }
    }
    header('HTTP/1.1 404 Not Found');
} else {
    if ($glob = glob("../img/c/$path*")) {
        $file = $glob[0];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . finfo_file($finfo, $file));
        finfo_close($finfo);
        readfile($file);
    }
    header('HTTP/1.1 404 Not Found');
}
