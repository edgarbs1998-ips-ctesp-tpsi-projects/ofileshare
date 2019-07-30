<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <title>Something went wrong!</title>
        <style type="text/css">
            html, body {
                background: #eee;
                padding: 10px;
            }
            div {
                margin: 0 auto;
                min-width: 650px;
                background-color: #fff;
                border: 1px solid #ccc;
                border-radius: 10px;
            }
            h1 {
                padding: 15px 30px;
                font: 20px Georgia, "Times New Roman", Times, serif;
                color: #f00;
            }
        </style>
    </head>
    <body>
        <div>
            <h1><?php echo APPEXCEPTION_MESSAGE; ?></h1>
        </div>
    </body>
</html>
