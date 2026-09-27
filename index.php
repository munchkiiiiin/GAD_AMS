<?php
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isBot = preg_match('/(facebookexternalhit|twitterbot|linkedinbot|whatsapp|skype|telegrambot|viber|slackbot|discordbot|applebot)/i', $userAgent);

if ($isBot && preg_match('/^\/gad-corner\/([0-9]+)/', $_SERVER['REQUEST_URI'], $matches)) {
    $id = $matches[1];
    $apiUrl = "https://bsugad.com/api/news-iec/" . $id;
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    curl_close($ch);

    $post = null;
    if ($response) {
        $data = json_decode($response, true);
        if (isset($data['success']) && $data['success'] && isset($data['data'])) {
            $post = $data['data'];
        }
    }

    if ($post) {
        $title = htmlspecialchars(trim(preg_replace('/\s+/', ' ', $post['title'] ?? 'GAD Corner Post')));
        $description = htmlspecialchars(trim(preg_replace('/\s+/', ' ', strip_tags($post['description'] ?? ''))));
        
        $imageUrl = '';
        if (!empty($post['image_path'])) {
            $images = json_decode($post['image_path'], true);
            if (is_array($images) && count($images) > 0) {
                $imageUrl = "https://bsugad.com/api/files/news-iec/" . urlencode($images[0]);
            } else if (is_string($images)) {
                $imageUrl = "https://bsugad.com/api/files/news-iec/" . urlencode($images);
            }
        }
        
        $currentUrl = "https://bsugad.com/gad-corner/" . $id;
        
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>'.$title.'</title>
    <meta name="description" content="'.$description.'">
    <meta property="og:type" content="article">
    <meta property="og:url" content="'.$currentUrl.'">
    <meta property="og:title" content="'.$title.'">
    <meta property="og:description" content="'.$description.'">';
        if ($imageUrl) {
            echo '
    <meta property="og:image" content="'.$imageUrl.'">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="twitter:image" content="'.$imageUrl.'">';
        }
        echo '
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="'.$currentUrl.'">
    <meta property="twitter:title" content="'.$title.'">
    <meta property="twitter:description" content="'.$description.'">
    <script>window.location.replace("'.$currentUrl.'");</script>
</head>
<body>
    <p>Redirecting...</p>
</body>
</html>';
        exit;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
  <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
  <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
  <link rel="manifest" href="/site.webmanifest">
  <link rel="icon" href="/favicon.ico" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>GAD-AMS</title>
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:opsz,wght@8..60,400;8..60,600;8..60,700&family=IBM+Plex+Sans:wght@400;500;600&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
  <script type="module" crossorigin src="/assets/index-BMXttjw7.js"></script>
  <link rel="modulepreload" crossorigin href="/assets/rolldown-runtime-hePW80VL.js">
  <link rel="stylesheet" crossorigin href="/assets/index-Vaudz5ru.css">
</head>

<body>
  <div id="app"></div>
</body>

</html>