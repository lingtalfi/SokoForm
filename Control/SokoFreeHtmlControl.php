<?php


namespace SokoForm\Control;


class SokoFreeHtmlControl extends SokoControl
{

    public function setHtml($html)
    {
        $this->setProperty("html", $html);
        return $this;
    }
}