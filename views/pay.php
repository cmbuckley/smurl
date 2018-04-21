<!doctype html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no">
    <title>Pay <?= $this->who ?></title>
    <style>
        body {
            text-align: center;
            font-family: Helvetica, Arial, sans;
            background-color: #ededed;
            color: #4d5c93;
        }

        ul {
            list-style-type: none;
            width: 20em;
            margin: 0 auto;
            padding: 0;
        }

        li {
            display: block;
            height: 5em;
            margin: 2em 1em;
            border: 1px solid #7d8cc3;
            border-radius: 5px;
            background-color: #deedff;
            background-repeat: no-repeat;
            background-position: center center;
            box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.2);
        }

        li:hover {
            background-color: #f4f9ff;
            box-shadow: 0px 0px 15px 0px rgba(0, 0, 0, 0.3);
        }

        a {
            display: block;
            height: 100%;
            width: 100%;
            text-indent: 100%;
            white-space: nowrap;
            overflow: hidden;
        }

        .paypal {
            background-image: url('https://www.paypalobjects.com/webstatic/mktg/Logo/pp-logo-200px.png');
        }

        .monzo {
            background-image: url('https://monzo.me/static/media/monzo-me-logo.957fd873.svg');
        }
    </style>
</head>
<body>
<h1>How do you want to pay <?= $this->who; ?>?</h1>

<ul>
    <li class="monzo"><a href="<?= $this->monzo; ?>">Monzo</a></li>
    <li class="paypal"><a href="<?= $this->paypal; ?>">PayPal</a></li>
</ul>
</body>
</html>
