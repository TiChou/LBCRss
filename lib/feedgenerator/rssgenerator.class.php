<?php
/**
 * RSSGenerator
 *
 * Generator strategy which creates XML for RSS
 *
 * @author Mateusz 'MatheW' Wójcik
 * @package    FeedGenerator
 *
 */
class RSSGenerator implements generator{

    private $_dom, $_channel, $_rssNode;
    private $channelRequired=array('title', 'link', 'description'),
    $channelDeleted=array('id', 'channelLink'), $channelChanged= array ('author' => 'managingEditor'),
    $itemRequired=array('title', 'link', 'description'),
    $itemChanged=array('id'=>'guid'),
    $itemDeleted=array('updated');

    public function __construct(){
        $this->_dom=new DOMDocument('1.0', 'utf-8');
        $this->_dom->formatOutput=true;
        $this->_rssNode=$this->_dom->appendChild($this->_dom->createElement('rss'));
        $this->_rssNode->setAttribute('version', '2.0');
    }

    public function generatorName(){
        return 'RSS FeedGenerator 1.1 by Mateusz \'MatheW\' Wójcik';
    }

    /**
     * Generates XML code
     *
     * @param Channel $channel
     * @return string
     */
    public function generate(Channel $channel){
        $this->_channel=$channel;
        $channel=$this->_rssNode->appendChild($this->_dom->createElement('channel'));

        foreach($this->_channel as $nodeName=>$nodeValue){
            if (in_array($nodeName, $this->channelDeleted))
                continue;
            if (in_array($nodeName, array_keys($this->channelChanged)))
                $nodeName= $this->channelChanged[$nodeName];
            if(!empty($nodeValue) or in_array($nodeName, $this->channelRequired)) {
                $element = $this->_dom->createElement($nodeName);
                $element->appendChild($this->_dom->createTextNode($nodeValue));
                $channel->appendChild($element);
            }
        }

        foreach($this->_channel->getItems() as $item){
            $i=$channel->appendChild($this->_dom->createElement('item'));
            foreach($item as $nodeName=>$nodeValue) {
                if(in_array($nodeName, $this->itemDeleted)) continue;
                if(in_array($nodeName, array_keys($this->itemChanged))) $nodeName=$this->itemChanged[$nodeName];
                if(!empty($nodeValue) or in_array($nodeName, $this->itemRequired)) {
                    $element = $this->_dom->createElement($nodeName);
                    switch($nodeName) {
                        case 'description':
                            $element->appendChild($this->_dom->createCDATASection($nodeValue));
                            break;
                        case 'guid':
                            if(!preg_match("#^https?://#", $nodeValue, $m))
                                $element->setAttribute('isPermaLink', 'false');
                        default:
                            $element->appendChild($this->_dom->createTextNode($nodeValue));
                    }
                    $i->appendChild($element);
                }
            }
        }

        return $this->_dom->saveXML();
    }
}
?>
