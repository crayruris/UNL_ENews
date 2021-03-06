<?php
UNL_ENews_PostRunFilter::setReplacementData('pagetitle', $context->subject);
UNL_ENews_PostRunFilter::setReplacementData('sitetitle', $context->newsroom->name);
if (isset($_GET['_type']) && $_GET['_type'] == 'subscribed') : ?>
<script type="text/javascript">
// This plugin is only needed for the demo.
WDN.initializePlugin('notice');
</script>
<div class="wdn_notice affirm">
    <div class="close">
        <a href="#" title="Close this notice">Close this notice</a>
    </div>
    <div class="message">
        <h4>Almost there!</h4>
        <p>We have received your subscription request. An email has been sent to your address asking you to confirm. Simply click the confimation link in
        that email, and you'll be set.
        </p>
    </div>
</div>
<?php
endif;
?>
<section class="wdn-grid-set">
    <div class="bp768-wdn-col-two-thirds">
        <div id="newsletterWeb">
            <?php echo $savvy->render($context->getStories(), 'ENews/Newsletter/StoriesWeb.tpl.php'); ?>

            <div style="clear:both;display:block;text-align:center;font-size:.8em;border-top:1px solid #E0E0E0;margin-top:5px;padding-top:5px">
                Originally published <?php echo date('F j, Y', strtotime($context->release_date)); ?>
                -
                <a href="<?php echo $context->newsroom->getSubmitURL(); ?>">Submit an Item</a>
            </div>
        </div>
    </div>
    <div class="bp768-wdn-col-one-third">
        <?php echo $savvy->render($context, 'ENews/Newsletter/SidebarNav.tpl.php'); ?>
    </div>
</section>
