<html>
<head>
<meta property="og:title" content="<?= $this->title; ?>" />
<meta property="og:description" content="<?= $this->desc; ?>" />
<meta property="og:url" content="<?= $this->requestUrl; ?>" />
<meta property="og:image" content="http://maps.googleapis.com/maps/api/streetview?size=200x200&location=<?= $this->lat . ',' . $this->lng; ?>&heading=<?= $this->heading; ?>&fov=<?= $this->zoom; ?>&key=<?= $this->key; ?>" />
<meta property="og:type" content="website" />
<meta http-equiv="refresh" content="0;url=maps:<?= $this->queryString; ?>" />
<meta name="viewport" content="width=device-width, maximum-scale=1" />
<link rel="shortcut icon" href="<?= $this->imgUrl; ?>/favicon.ico">
<style>
@media only screen and (min-width: 460px) {
    .message { border-radius: 8px; }
}
body {
    font-family: -apple-system,system-ui,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,sans-serif;
    font-size: 15px;
    color: #050505;
    background-color: #f0f2f5;
    margin: 0;
}
.message {
    background-color: white;
    margin: 8px auto 0;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    max-width: 450px;
}
.message-inner {
    padding: 8px;
    padding-bottom: 0;
}
.message-inner > *:last-child {
    padding-bottom: 15px;
    margin-bottom: 0;
    border-bottom: 1px solid #ced0d4;
}
.message-img {
    float: left;
    margin-right: 10px;
}
.message a {
    color: #050505;
    text-decoration: none;
}
.message a:hover {
    text-decoration: underline;
}
.message-head {
    overflow: hidden;
}
.message h1 {
    margin: 0;
    font-size: 15px;
    font-weight: 500;
}
.subhead {
    margin: 2px 0;
    color: #65676b;
    font-size: 12.5px;
}
.map {
    border: 1px solid #ccc;
    width: 100%;
}
.message-footer {
    padding: 7px 8px;
    font-size: 13px;
    display: flex;
}
.message-footer a {
    color: #65676b;
}
.footer-link {
    padding: 5px;
    flex: 1;
    text-align: center;
}
</style>
</head>
<body>
<div class=message>
<div class=message-inner>
<img class=message-img width=34 height=34 src="http://maps.googleapis.com/maps/api/streetview?size=200x200&location=<?= $this->lat . ',' . $this->lng; ?>&heading=<?= $this->heading; ?>&fov=<?= $this->zoom; ?>&key=<?= $this->key; ?>" />
<div class=message-head>
<h1><a href="maps:<?= $this->queryString; ?>"><?= $this->title; ?></a></h1>
<p class=subhead><?= $this->requestUrl; ?></p>
</div>
<p><?= $this->desc; ?></p>
<p><a href="maps:<?= $this->queryString; ?>"><img class=map src="http://maps.googleapis.com/maps/api/staticmap?center=<?= $this->address; ?>&zoom=16&size=280x200&scale=2&maptype=roadmap&sensor=false&markers=<?= $this->address; ?>&key=<?= $this->key; ?>" /></a></p>
</div>
<div class=message-footer>
<div class=footer-link><a href="maps:<?= $this->queryString; ?>">Open in iOS Maps</a></div>
<div class=footer-link><a href="comgooglemaps://?<?= $this->queryStringSimple; ?>">Open in Google Maps</a></div>
</div>
</div>
</body>
</html>
