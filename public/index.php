<?php

class Template extends ArrayObject {
    protected $_templateName;

    public function __construct($name, array $input = array()) {
        $this->_templateName = $name;
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

function qsa($url) {
    return $url . (strpos($url, '?') ? '&' : '?') . $_SERVER['QUERY_STRING'];
}

function map($address, $title, $img) {
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
            $desc  = 'A map of ' . preg_replace('/\s*\(.+?\)$/', '', $query['q']) . '.';

            $body  = <<<EOS
<html>
<head>
<meta property="og:title" content="$title" />
<meta property="og:description" content="$desc" />
<meta property="og:url" content="$requestUrl" />
<meta property="og:image" content="http://maps.googleapis.com/maps/api/streetview?size=200x200&location={$img[0]},{$img[1]}&heading={$img[2]}&fov={$img[3]}" />
<meta property="og:type" content="website" />
<meta http-equiv="refresh" content="0;url=maps:$queryString" />
<meta name="viewport" content="width=device-width, maximum-scale=1" />
<style>
body {
    font-family: helvetica, sans-serif;
    font-size: 14px;
    background-color: #c4cde0;
    margin: 0;
}
.message {
    background-color: white;
    margin: 10px;
    border: 1px solid #b4bccd;
    border-radius: 3px;
    box-shadow: 0 1px 3px -2px rgba(0, 0, 0, 0.5);
}
.message-inner {
    padding: 8px;
}
.message-img {
    float: left;
    margin-right: 10px;
}
.message a, .message-footer {
    color: #576B95;
    font-weight: bold;
    text-decoration: none;
}
.message-head {
    overflow: hidden;
}
.message h1 {
    margin: 0;
    font-size: 13px;
}
.subhead {
    margin: 2px 0;
    color: #bbb;
    font-size: 12.5px;
}
.map {
    border: 1px solid #ccc;
    width: 280px;
    height: 200px;
}
.message-footer {
    background-color: #eff2f5;
    border-top: 1px solid #DADDE1;
    padding: 7px 8px;
    font-size: 12px;
}
.message-footer a {
    margin: 0 5px;
}
.message-footer a:first-child {
    margin-left: 0;
}
</style>
</head>
<body>
<div class=message>
<div class=message-inner>
<img class=message-img width=34 height=34 src="http://maps.googleapis.com/maps/api/streetview?size=200x200&location={$img[0]},{$img[1]}&heading={$img[2]}&fov={$img[3]}" />
<div class=message-head>
<h1><a href="maps:$queryString">$title</a></h1>
<p class=subhead>{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}</p>
</div>
<p>$desc</p>
<p><a href="maps:$queryString"><img class=map src="http://maps.googleapis.com/maps/api/staticmap?center=$address&zoom=16&size=280x200&scale=2&maptype=roadmap&sensor=false&markers=$address" /></a></p>
</div>
<div class=message-footer><a href="maps:$queryString">Open in iOS Maps</a><a href="comgooglemaps://?$queryStringSimple">Open in Google Maps</a></div>
</div>
</body>
</html>
EOS;
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
        $newPath = preg_replace("#$pattern#", $replacement, $path, -1, $count);

        if ($count) {
            header('Location: ' . $newPath);
            return;
        }
    }
    header('HTTP/1.1 404 Not Found');
} else {
    if ($glob = glob(".img/$path*")) {
        $file = $glob[0];
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        header('Content-Type: ' . finfo_file($finfo, $file));
        finfo_close($finfo);
        readfile($file);
    }
    header('HTTP/1.1 404 Not Found');
}
