<?php namespace Plugins\Title\Handlers;

/**
 * If this wasn't so handy I wouldn't even bother making this.
 * Newegg names this a PAIN to work, since they load prices thought jAVASCRIPT
 * Do not judge my coding on this class, unless, y'kno, it's actually coded well.. Otherwise disregard this.
 *
 * IF THERE ISN'T AN EASIER WAY TO DO THIS, SCREW IT ALL.
 */


use Dan\Irc\Location\Channel;
use Plugins\Title\Handler;
use Plugins\Title\HandlerInterface;

class NeweggHandler extends Handler implements HandlerInterface  {

    protected $domains = [
        'www.newegg.com'
    ];

    /**
     * @inheritdoc
     */
    public function handleLink(Channel $channel, array $headers, $link)
    {
        $linkCheck = parse_url($link);


        if($linkCheck['path'] !== '/Product/Product.aspx')
        {
            $handler = new WebpageHandler();
            $handler->handleLink($channel, $headers, $link);
            unset($handler);
            return;
        }

        $data = file_get_contents($link);
        $data = mb_convert_encoding($data, 'HTML-ENTITIES', "UTF-8");

        $dom = new \DOMDocument();
        $dom->resolveExternals = false;
        $dom->strictErrorChecking = false;

        @$dom->loadHTML($data);

        $xpath          = new \DOMXPath($dom);
        $productData    = [];

        foreach($dom->getElementsByTagName('script') as $script)
        {
            $textContent = trim($script->textContent);

            if(strpos($textContent, 'var utag_data')  === false)
                continue;

            $productData = $this->parseJavascript($textContent);
            break;
        }

        $title      = $productData['product_title'][0];
        $shipping   = $productData['product_default_shipping_cost'][0];
        $unitPrice  = $productData['product_unit_price'][0];
        $salePrice  = $productData['product_sale_price'][0];
        $eggGetter  = $xpath->query("//a[contains(@class, 'itmRating')]/span[contains(@itemprop, 'ratingValue')]");

        $eggs       = null;
        $price      = $unitPrice;
        $reviews    = null;
        $saleDiff   = '';

        if($unitPrice != $salePrice)
        {
            $price      = $salePrice;
            $saleDiff   = ' ({white:green}-$' . ($unitPrice - $salePrice) .'{yellow})';
        }

        if($eggGetter->length !== 0)
        {
            $eggs       = trim($eggGetter->item(0)->attributes->getNamedItem('content')->textContent);
            $reviews    = trim($xpath->query("//a[contains(@class, 'itmRating')]/span[contains(@itemprop, 'reviewCount')]")->item(0)->textContent);
        }

        $rating = ($eggs == null ? "No Reviews" : "{$eggs}/5 eggs ({$reviews} reviews)");
        $shipping = $shipping == '0.01' ? 'Free' : "\${$shipping}";

        $channel->sendMessage("{reset}[{cyan} {$title} {reset}|{yellow} \${$price}{$saleDiff} {reset}|{light_green} Shipping: {$shipping} {reset}|{orange} {$rating} {reset}]");
    }


    /**
     * Parse that stupid javascript thing.
     *
     * @param $js
     * @return array
     */
    protected function parseJavascript($js)
    {
        $lines = explode("\n", str_replace(['var utag_data = ', '};'], '', $js));
        $data = [];

        foreach($lines as $line)
        {
            $js = explode(':', trim($line), 2);

            if(count($js) != 2)
                continue;

            $data[$js[0]] = $this->parseValue($js[1]);
        }

        return $data;
    }

    /**
     * Parse that stupid javascript value.
     *
     * @param $value
     * @return array|null|string
     */
    protected function parseValue($value)
    {
        $parsed = null;
        $chars  = str_split($value);
        $inArr  = false;
        $inStr  = false;
        $buffer = '';
        $arrBuff = [];

        for($i = 0; $i < count($chars); $i++)
        {
            if(!$inArr && !$inArr && $chars[$i] == ',')
                continue;

            if($chars[$i] == '[')
            {
                $inArr = true;
                continue;
            }

            if($chars[$i] == ']')
            {
                $arrBuff[] = $this->cleanString($buffer);
                $buffer = '';
                $inArr = false;
                $parsed = $arrBuff;
                continue;
            }

            if($chars[$i] == "'" || $chars[$i] == "'")
            {
                if($inStr && !$inArr)
                {
                    $parsed = $this->cleanString($buffer);
                    $buffer = '';
                }

                $inStr = ($chars[$i] == "'");
                continue;
            }


            if($inArr && $chars[$i] == ',')
            {
                $arrBuff[] = $this->cleanString($buffer);
                $buffer = '';
                continue;
            }

            $buffer .= $chars[$i];

        }

        return $parsed;
    }

    /**
     * @param $string
     * @return mixed|string
     */
    protected function cleanString($string)
    {
        $string = str_replace('&amp;', "&", $string);
        $string = htmlspecialchars_decode($string);
        $string = html_entity_decode($string);
        return $string;
    }
}
