<html>
<head>
<meta property="og:title" content="<?= $this->title; ?>" />
<meta property="og:description" content="<?= $this->desc; ?>" />
<meta property="og:url" content="<?= $this->requestUrl; ?>" />
<meta property="og:image" content="http://maps.googleapis.com/maps/api/streetview?size=200x200&location=<?= $this->lat . ',' . $this->lng; ?>&heading=<?= $this->heading; ?>&fov=<?= $this->zoom; ?>&key=<?= $this->key; ?>" />
<meta property="og:type" content="website" />
<meta http-equiv="refresh" content="0;url=maps:<?= $this->queryString; ?>" />
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
<img class=message-img width=34 height=34 src="http://maps.googleapis.com/maps/api/streetview?size=200x200&location=<?= $this->lat . ',' . $this->lng; ?>&heading=<?= $this->heading; ?>&fov=<?= $this->zoom; ?>&key=<?= $this->key; ?>" />
<div class=message-head>
<h1><a href="maps:<?= $this->queryString; ?>"><?= $this->title; ?></a></h1>
<p class=subhead><?= $this->requestUrl; ?></p>
</div>
<p><?= $this->desc; ?></p>
<p><a href="maps:<?= $this->queryString; ?>"><img class=map src="http://maps.googleapis.com/maps/api/staticmap?center=<?= $this->address; ?>&zoom=16&size=280x200&scale=2&maptype=roadmap&sensor=false&markers=<?= $this->address; ?>&key=<?= $this->key; ?>" /></a></p>
</div>
<div class=message-footer><a href="maps:<?= $this->queryString; ?>">Open in iOS Maps</a><a href="comgooglemaps://?<?= $this->queryStringSimple; ?>">Open in Google Maps</a></div>
</div>
</body>
</html>
