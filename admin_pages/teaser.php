<div class="teaser_cb">
    <button type="button" class="notice-dismiss js-close-div-spec"><span class="screen-reader-text">Скрыть это уведомление.</span></button>
    <div class="teaser_header"><?= __('Want to Get Much More Money from Your Website?', 'custom-blocks-free'); ?></div>
    <ul>
        <li><?= __('Get cases on monetization methods?', 'custom-blocks-free'); ?></li>
        <li><?= __('Be among the first to know about new features?', 'custom-blocks-free'); ?></li>
        <li><?= __('Get individual discounts on our products and consultations?', 'custom-blocks-free'); ?></li>
    </ul>
    <div class="block_teaser_first">
        <form method="post" action="https://easier.press/formreg.php" class="js-promo-send-ajax js-spec-send-data-first-form">
            <?PHP $current_user = wp_get_current_user(); ?>
            <input type="hidden" name="firstname" value="<?= $current_user->user_firstname; ?>" >
            <input type="hidden" name="lastname" value="<?= $current_user->user_lastname; ?>">
            <input type="hidden" name="email" value="<?= $current_user->user_email; ?>">
            <input type="hidden" name="tag_1" value="custom-blocks-free">
            <input type="hidden" name="tag_2" value="<?= get_locale(); ?>">
            <input type="hidden" name="form_id" value="cb_free">
            <input type="submit" value="<?= __('Yes, I do!', 'custom-blocks-free'); ?>" >
        </form>
        <?PHP wp_get_current_user(); ?>
        <span><?= __('After pressing “Yes, I do” you will be subscribed to our mailing list about website monetization.', 'custom-blocks-free'); ?><br><?= __('Your user e-mail will be used. For entering another name and e-mail', 'custom-blocks-free'); ?> <a class="js-teaser-change-email" href="#"><?= __('click here', 'custom-blocks-free'); ?></a>.</span>    
    </div>
    <div class="block_teaser_two">
        <form method="post" action="https://easier.press/formreg.php" class="js-promo-send-ajax">
            <input type="text" name="firstname" class="" value="" placeholder="<?= __('First Name', 'custom-blocks-free'); ?>">
            <input type="text" name="lastname" class="" value="" placeholder="<?= __('Last Name', 'custom-blocks-free'); ?>">
            <input type="email" name="email" required="" class="" value="" placeholder="<?= __('E-mail', 'custom-blocks-free'); ?>">
            <input type="hidden" name="tag_1" value="custom-blocks-free">
            <input type="hidden" name="tag_2" value="<?= get_locale(); ?>">
            <input type="hidden" name="form_id" value="cb_free">
            <input type="submit" name="" class="" value="<?= __('Subscribe', 'custom-blocks-free'); ?>">
        </form>
        <?= __('You may use your user name and e-mail for subscribing. Just', 'custom-blocks-free'); ?> <a class="js-teaser-change-email-two" href="#"><?= __('click here', 'custom-blocks-free'); ?></a>.
    </div>


</div>

<style>
    .teaser_cb {position:relative;}
    .block_teaser_two,
    .teaser_other_page 
    {
        display: none;
    }
    .teaser_cb {
        margin: 15px 0 0;
        padding: 14px 16px 8px; 
        border-left: solid 4px #ffd24d;
        border-bottom: solid 2px #dddddd;
        background: #fff;
    }
    .teaser_header {
        font-size: 20px;
        font-weight: bold;
        color: #000;
    }
    .teaser_cb ul {
        margin: 15px 0 18px;
        list-style: disc;
        padding-left: 25px;
        font-size: 15px;

    }
    .teaser_cb ul li {
        margin-bottom: 3px;
    }
    .block_teaser_first {
        font-style: italic;
        color: #a5a5a5;
    }
    .block_teaser_first a {
        color: #a5a5a5;
    }
    .block_teaser_first input[type="submit"] {
        display: block;
        padding: 9px 71px;
        margin-right: 13px;
        border: none;
        border: solid 1px #006698;
        border-bottom: solid 3px #006698;
        border-radius: 3px;
        background: #0084bc;
        color: #fff;
        font-size: 20px;
        cursor: pointer;
    }
    .block_teaser_first form {
        float: left;
    }
    .block_teaser_first span {
        display: inline-block;
        margin: 6px 0 15px;
    }
    .block_teaser_two {
        font-size: 12px;
        font-style: italic;
        color: #999999;
    }
    .block_teaser_two a {
        color: #999999;
    }
    .block_teaser_two input[type="text"],.block_teaser_two input[type="email"] {
        height: 46px;
        width: 230px;
        padding: 0 20px;
        margin-bottom: 10px;
        border: solid 2px #dedede;
        border-radius: 2px;
        background: #f8f8f8;
    }
    .block_teaser_two input[type="submit"] {
        padding: 0 71px;
        border: none;
        height: 44px;
        border: solid 1px #006698;
        border-radius: 3px;
        background: #0084bc;
        color: #fff;
        font-size: 16px; 
        cursor: pointer;
    }
    p.promo_header {
        margin: 17px 0 15px; 
        font-size: 30px !important;
    }
    ul.landing_prices {
        margin-left: 0 !important;
    }
    .price_unlim_comment, .price_unlim_comment p {
        margin-left: 0 !important;
    }
</style>
<script>
    jQuery(document).ready(function ($) {
        $('.js-teaser-change-email').click(function () {
            $('.block_teaser_first').hide();
            $('.block_teaser_two').show();
        });

        $('.js-teaser-change-email-two').click(function () {
            $('.js-spec-send-data-first-form').submit();
        });


        $('.js-promo-send-ajax').submit(function () {
            var ajax_url_send = $(this).attr('action');
            var firstname = $(this).find('input[name="firstname"]').val();
            var lastname = $(this).find('input[name="lastname"]').val();
            var email = $(this).find('input[name="email"]').val();
            var form_id = $(this).find('input[name="form_id"]').val();
            var tag_1 = $(this).find('input[name="tag_1"]').val();
            var tag_2 = $(this).find('input[name="tag_2"]').val();
            $.ajax({
                type: 'POST',
                url: ajax_url_send,
                data: {
                    firstname: firstname,
                    lastname: lastname,
                    email: email,
                    form_id: form_id,
                    tag_1:tag_1,
                    tag_2:tag_2
                },
                success: function (data) {
                    alert('<?= __("You are successfully subscribed.", 'custom-blocks-free'); ?>');
                },
                error: function (response) {
                }
            });
            return false;
        });

    });
</script>