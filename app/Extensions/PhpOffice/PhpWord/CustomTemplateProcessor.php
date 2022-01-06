<?php

namespace App\Extensions\PhpOffice\PhpWord;

class CustomTemplateProcessor extends \PhpOffice\PhpWord\TemplateProcessor
{
    public function __construct($documentTemplate)
    {
        parent::__construct($documentTemplate);
    }

    protected function setValueForPart($search, $replace, $documentPartXML, $limit)
    {
        $replace = preg_replace('/\\\n/', '<w:br/>', $replace); // replace \n with 'newline'
        $replace = preg_replace('/\\\s/', '<w:t xml:space="preserve"> </w:t>', $replace); // replace \s with 'space'
        $replace = preg_replace('/&lt;strong&gt;/', '</w:t></w:r><w:r><w:rPr><w:b/></w:rPr><w:t xml:space="preserve">', $replace);
        $replace = preg_replace('/&lt;\/strong&gt;/', '</w:t></w:r><w:r><w:t xml:space="preserve">', $replace);
        $replace = preg_replace('/&lt;em&gt;/', '</w:t></w:r><w:r><w:rPr><w:i/></w:rPr><w:t xml:space="preserve">', $replace);
        $replace = preg_replace('/&lt;\/em&gt;/', '</w:t></w:r><w:r><w:t xml:space="preserve">', $replace);
        $replace = preg_replace('/&lt;u&gt;/', '</w:t></w:r><w:r><w:rPr><w:u w:val="single"/></w:rPr><w:t xml:space="preserve">', $replace);
        $replace = preg_replace('/&lt;\/u&gt;/', '</w:t></w:r><w:r><w:t xml:space="preserve">', $replace);

        return parent::setValueForPart($search, $replace, $documentPartXML, $limit);
    }

}
