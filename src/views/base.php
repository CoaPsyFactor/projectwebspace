<!DOCTYPE html>
<html>
    <head>
        <title><?php echo $title ?></title>
    </head>
    <body>
        <div class="header">
            <?php $template->showSection('header') ?>
        </div>
        <div class="content">
            <?php $template->showSection('content') ?>
        </div>
    </body>
</html>

