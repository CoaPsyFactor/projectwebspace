<?php /** @var \App\Modules\Template $template */ $template->setParent('views/base.php') ?>

<?php $template->setSectionContent('content', function () { ?>
    <form method="post">
        <input type="text" placeholder="username" /><br>
        <input type="password" placeholder="password" /><br>
        <button type="submit">Login</button>
    </form>
<?php }) ?>