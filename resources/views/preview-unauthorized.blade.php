<!doctype html>
<html lang="fr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Unauthorized</title>
        <style>
            body {
                font-family: Figtree,ui-sans-serif,system-ui,sans-serif,"Apple Color Emoji","Segoe UI Emoji",Segoe UI Symbol,"Noto Color Emoji";
                line-height: 1.5;
            }
        </style>
    </head>
    <body>
        <div style="display: flex; justify-content: center; align-items: center; height: 100vh">
            <div style="padding: 2rem; background-color: #ffb; max-width: 400px">
                Le lien de prévisualisation a expiré, veuillez l’ouvrir de nouveau depuis le menu de votre <a href="{{ rtrim(config('ozu-client.api_host'), '/') }}/sharp">tableau de bord</a>.
            </div>
        </div>
    </body>
</html>
