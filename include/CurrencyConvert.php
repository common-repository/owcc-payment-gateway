<?php

class CurrencyConvert {

    public function owccpg_bitcon($cur) {

        return wp_remote_retrieve_body(wp_remote_get("https://blockchain.info/tobtc?currency=USD&value=$cur"));
        wp_die();
    }

    public function owccpg_stellarxlm() {

        return wp_remote_retrieve_body(wp_remote_get('https://min-api.cryptocompare.com/data/price?fsym=USD&tsyms=XLM&amnt=1'));
        wp_die();
    }

    public function owccpg_qrcode($code) {

        $response = wp_remote_retrieve_body(wp_remote_get("https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=$code"));
        __("<img src=" . $response . "/>", 'pwn-OWCC-gateway');
        wp_die();
    }

    public function owccpg_bitrate() {
        $url = 'https://bitpay.com/api/rates';
        $json = json_decode(wp_remote_retrieve_body(wp_remote_get($url)));
        $dollar = $btc = 0;

        foreach ($json as $obj) {
            if ($obj->code == 'USD')
                $btc = $obj->rate;
        }

        return "1 bitcoin = &nbsp; $" . $btc . " (USD)";
    }

}
