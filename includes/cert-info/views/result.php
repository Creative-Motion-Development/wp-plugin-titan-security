<?php

use WBCR\Titan\Cert\Cert;

/**
 * @var Cert $cert
 */

$securedUrl = get_site_url( null, '', 'https' );
?>
<div class="row">
    <div class="col-md-12">
		<?php if ( $cert->is_available() ): ?>
			<?php if ( $cert->is_lets_encrypt() ): ?>
				Все в порядке. Сертификат будет обновляться автоматически шаред хостингом
			<?php else: ?>
                Сертификат заканчивается <?php echo date( 'd-m-Y H:i:s', $cert->get_expiration_timestamp() ) ?>
			<?php endif ?>
		<?php else: ?>
			<?php switch ( $cert->get_error() ): ?>
<?php case Cert::ERROR_UNAVAILABLE: ?>
                    Нет расширения openssl для php
					<?php break;
				case Cert::ERROR_ONLY_HTTPS: ?>
                    Доступно только на <a href="<?php echo $securedUrl ?>"><?php echo $securedUrl ?></a>
					<?php break;
				case Cert::ERROR_HTTPS_UNAVAILABLE: ?>
                    https на этом сайте недоступен
					<?php break;
				case Cert::ERROR_UNKNOWN_ERROR: ?>
                    Неизвестная ошибка
					<?php break; endswitch ?>
		<?php endif ?>
    </div>

    <div class="col-md-12">
        Ошибка (возвращает объект Cert): <?php echo $cert->get_error_message() ?>
    </div>

</div>
