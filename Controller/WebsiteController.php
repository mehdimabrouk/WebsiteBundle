<?php
/**
 * Created by PhpStorm.
 * User: panos
 * Date: 7/8/14
 * Time: 10:46 AM
 */

namespace Icap\WebsiteBundle\Controller;

use Icap\WebsiteBundle\Entity\Website;
use Icap\WebsiteBundle\Entity\WebsitePageTypeEnum;
use Icap\WebsiteBundle\Form\WebsitePageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;


class WebsiteController extends Controller{

    /**
     * @Route(
     *      "/{websiteId}",
     *      requirements={"websiteId" = "\d+"},
     *      name="icap_website_view",
     *      options={"expose"=true}
     * )
     * @ParamConverter("website", class="IcapWebsiteBundle:Website", options={"id" = "websiteId"})
     */
    public function viewAction(Website $website)
    {
        $this->checkAccess("OPEN", $website);
        $isAdmin = $this->isUserGranted("ADMINISTRATE", $website);
        $user = $this->getLoggedUser();
        $pageManager = $this->getWebsitePageManager();

        $viewArray = array(
            '_resource' => $website,
            'workspace' => $website->getResourceNode()->getWorkspace(),
            'user' => $user,
            'pageTypes' => array(
                'blank' => WebsitePageTypeEnum::BLANK_PAGE,
                'resource' => WebsitePageTypeEnum::RESOURCE_PAGE,
                'url' => WebsitePageTypeEnum::URL_PAGE
            )
        );
        if($isAdmin) {
            $pages = $pageManager->getPageTree($website, $isAdmin, false);
            $website->setPages($pages);
            $resourceTypes = $this->get('claroline.manager.resource_manager')->getAllResourceTypes();
            $viewArray['resourceTypes'] = $resourceTypes;

            return $this->render("IcapWebsiteBundle:Website:edit.html.twig", $viewArray);
        } else {
            $pages = $pageManager->getPageTree($website, $isAdmin, true);
            $website->setPages($pages);
            $currentPage = null;
            if(!empty($pages) && !empty($pages[0]['children'])){
                $currentPage = $pages[0]['children'][0];
            }
            if (isset($currentPage) && $currentPage !== null) {
                $currentPage = $pageManager->getPages($website, $currentPage['id'], $isAdmin, false)[0];
            }

            $viewArray['currentPage'] = $currentPage;
        }

        return $this->render("IcapWebsiteBundle:Website:view.html.twig", $viewArray);
    }

} 