<rss version="2.0">
    <channel>
        <title>News from <?php echo UNL_ENews_Newsroom::getByID($context->options['newsroom'])->name; ?></title>
        <link><?php echo UNL_ENews_Controller::getURL(); ?></link>
        <description>Latest news from <?php echo UNL_ENews_Newsroom::getByID($context->options['newsroom'])->name; ?></description>
        <language>en-us</language>
        <generator>Magic</generator>
        <lastBuildDate><?php echo date('r'); ?></lastBuildDate>
    </channel>   
    <?php echo $savvy->render($context->actionable); ?>
</rss>