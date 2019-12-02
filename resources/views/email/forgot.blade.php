<html>
<head>
    <meta charset="UTF-8">
    <title>yazilim.xyz</title>
</head>
<body>
<div>
    <h1>Parola Sıfırla</h1>
    <p>
        Merhaba {{ $username }},<br>
        {{ $content }}
    </p>
    <div>
        <a href="{{ $url }}">Parola Sıfırla</a>
    </div>
    <p>
        Butona tıklayamıyorsanız bu linki tarayıcınızın adres çubuğuna yapıştırınız: <span>{{ $url }}</span>
    </p>
</div>
</body>
</html>