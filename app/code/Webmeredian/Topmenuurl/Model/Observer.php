<?php
namespace Webmeredian\Topmenuurl\Model;
use Magento\TestFramework\Event\Magento;

class Observer implements \Magento\Framework\Event\ObserverInterface
{

    protected $_objectManager;
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_objectManager = $objectmanager;
    }
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private $scopeConfig;



    /**
     * @param $id
     * @param \Magento\Framework\Data\Tree\Node $node
     */
    private function setUrlById($url, $newUrl, \Magento\Framework\Data\Tree\Node $node){

        if($node->getUrl() == $url){
            $flag = 1;
            $node->setUrl($newUrl);
            return $node;
        }

        $children = $node->getChildren();
        $flag = 0;

        foreach ($children->getNodes() as $key=>$_nodeChild) {
            if(count($_nodeChild->getChildren())>0){
                $_nodeChildAfter = $this->setUrlById($url, $newUrl, $_nodeChild);
                if($_nodeChildAfter){
                    $node->getChildren()->getNodes()[$key] = $_nodeChildAfter;
                    return $node;
                }
            }
        }

        return null;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Framework\Data\Tree\Node $menu */
        $menu = $observer->getMenu();
        $children = $menu->getChildren();
        $itemsSerialized = $this->scopeConfig->getValue('topmenu/topmenuurl/urls', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        if($itemsSerialized){
            $items = unserialize($itemsSerialized);

            foreach ($items as $item) {
                $url = $item['old_url'];//'http://magento23.loc/women/tops-women.html';
                $new_url = $item['new_url'];//'http://magento23.loc/111111111111.html';

//                $_nodeChild = $menu;
//                foreach ($children->getNodes() as $key=>$_nodeChild) {
                    $_menuAfter = $this->setUrlById($url, $new_url, $menu);
                    if($_menuAfter){
                        //$menu->getChildren()->getNodes()[$key] = $_nodeChildAfter;
                        $observer->setData('menu',$_menuAfter);
                    }
//                }

            }

        }

        return $this;
    }
}
