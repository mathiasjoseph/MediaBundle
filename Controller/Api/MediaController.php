<?php



namespace Miky\Bundle\MediaBundle\Controller\Api;

use FOS\RestBundle\Context\Context;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\Annotations\Route;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Request\ParamFetcherInterface;
use FOS\RestBundle\View\View as FOSRestView;
use JMS\Serializer\SerializationContext;
use Miky\Bundle\MediaBundle\Model\MediaManagerInterface;
use Miky\Bundle\MediaBundle\Provider\MediaProviderInterface;
use Miky\Bundle\MediaBundle\Provider\Pool;
use Miky\Component\Media\Model\Media;
use Miky\Component\Media\Model\MediaInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Sonata\DatagridBundle\Pager\PagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MediaController.
 *
 * Note: Media is plural, medium is singular (at least according to FOSRestBundle route generator)
 *
 *
 * @author Hugo Briand <briand@ekino.com>
 */
class MediaController extends FOSRestController
{

    /**
     * @param Request $request
     * @Rest\Post(name="miky_media_api_media_upload", options={ "method_prefix" = false })
     *
     * @return Response
     */
    public function uploadAction(Request $request)
    {
            $context = $request->get("context", "default");
            $provider = $request->get("provider");
            $mediaToken = $request->get("media_token");
            $mediaManager= $this->get("miky.media.manager.media");
            /** @var Media $media */
            $media = $mediaManager->create();
            $media->setBinaryContent($request->files->get('file'));
            $media->setContext($context);
            $media->setProviderName($provider);
            $media->setToken($mediaToken);
            $media->setTemporary(true);
            $date = new \DateTime();
            $date->add(new \DateInterval('PT1H'));
            $media->setExpiresAt($date);
            $mediaManager->save($media);

        $view = $this->view($media, 200);

        return $this->handleView($view);
    }

    /**
     * @param Request $request
     * @Rest\Post("/media/{id}/remove", name="miky_media_api_media_remove", options={ "method_prefix" = false })
     * @return Response
     */
    public function removeAction(Request $request, $id)
    {

        $mediaManager= $this->get("miky.media.manager.media");
        $media = $mediaManager->find($id);
        if($media !=null){
            $mediaManager->delete($media);
            $result = array("removed" => true);
        }else{
            $result = array("removed" => false);
        }
        $view = $this->view($result, 200);

        return $this->handleView($view);
    }

    /**
     * Retrieves the list of medias (paginated).
     *
     * @ApiDoc(
     *  resource=true,
     *  output={"class"="Sonata\DatagridBundle\Pager\PagerInterface", "groups"="miky_api_read"}
     * )
     *
     * @QueryParam(name="page", requirements="\d+", nullable=true, default="1", description="Page for media list pagination")
     * @QueryParam(name="count", requirements="\d+", nullable=true, default="10", description="Number of medias by page")
     * @QueryParam(name="enabled", requirements="0|1", nullable=true, strict=true, description="Enabled/Disabled medias filter")
     * @QueryParam(name="orderBy", map=true, requirements="ASC|DESC", nullable=true, strict=true, description="Order by array (key is field, value is direction)")
     *
     * @View(serializerGroups="miky_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return PagerInterface
     */
    public function getMediaAction(ParamFetcherInterface $paramFetcher)
    {
        $mediaManager= $this->get("miky.media.manager.media");
        $supportedCriteria = array(
            'enabled' => '',
        );

        $page = $paramFetcher->get('page');
        $limit = $paramFetcher->get('count');
        $sort = $paramFetcher->get('orderBy');
        $criteria = array_intersect_key($paramFetcher->all(), $supportedCriteria);

        foreach ($criteria as $key => $value) {
            if (null === $value) {
                unset($criteria[$key]);
            }
        }

        if (!$sort) {
            $sort = array();
        } elseif (!is_array($sort)) {
            $sort = array($sort => 'asc');
        }

        return $mediaManager->getPager($criteria, $page, $limit, $sort);
    }

    /**
     * Retrieves a specific media.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"}
     *  },
     *  output={"class"="Miky\Bundle\MediaBundle\Model\Media", "groups"="miky_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @View(serializerGroups="miky_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     *
     * @return MediaInterface
     */
    public function getMediumAction($id)
    {
        return $this->getMedium($id);
    }

    /**
     * Returns media urls for each format.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @param $id
     *
     * @return array
     */
    public function getMediumFormatsAction($id)
    {
        $mediaPool = $this->get("miky.media.pool");
        $media = $this->getMedium($id);

        $formats = array('reference');
        $formats = array_merge($formats, array_keys($mediaPool->getFormatNamesByContext($media->getContext())));

        $provider = $mediaPool->getProvider($media->getProviderName());

        $properties = array();
        foreach ($formats as $format) {
            $properties[$format]['url'] = $provider->generatePublicUrl($media, $format);
            $properties[$format]['properties'] = $provider->getHelperProperties($media, $format);
        }

        return $properties;
    }

    /**
     * Returns media binary content for each format.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="media id"},
     *      {"name"="format", "dataType"="string", "description"="media format"}
     *  },
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @param int     $id      The media id
     * @param string  $format  The format
     * @param Request $request
     *
     * @return Response
     */
    public function getMediumBinaryAction($id, $format, Request $request)
    {
        $media = $this->getMedium($id);
        $mediaPool = $this->get("miky.media.pool");

        $response = $mediaPool->getProvider($media->getProviderName())->getDownloadResponse($media, $format, $mediaPool->getDownloadMode($media));

        if ($response instanceof BinaryFileResponse) {
            $response->prepare($request);
        }

        return $response;
    }

    /**
     * Deletes a medium.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="medium identifier"}
     *  },
     *  statusCodes={
     *      200="Returned when medium is successfully deleted",
     *      400="Returned when an error has occurred while deleting the medium",
     *      404="Returned when unable to find medium"
     *  }
     * )
     *
     * @param int $id A medium identifier
     *
     * @return View
     *
     * @throws NotFoundHttpException
     */
    public function deleteMediumAction($id)
    {
        $medium = $this->getMedium($id);
        $mediaManager = $this->get("miky.media.manager.media");
        $mediaManager->delete($medium);

        return array('deleted' => true);
    }

    /**
     * Updates a medium
     * If you need to upload a file (depends on the provider) you will need to do so by sending content as a multipart/form-data HTTP Request
     * See documentation for more details.
     *
     * @ApiDoc(
     *  requirements={
     *      {"name"="id", "dataType"="integer", "requirement"="\d+", "description"="medium identifier"}
     *  },
     *  input={"class"="miky_media_api_form_media", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Miky\Bundle\MediaBundle\Model\Media", "groups"={"miky_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while medium update",
     *      404="Returned when unable to find medium"
     *  }
     * )
     *
     * @param int     $id      A Medium identifier
     * @param Request $request A Symfony request
     *
     * @return MediaInterface
     *
     * @throws NotFoundHttpException
     */
    public function putMediumAction($id, Request $request)
    {
        $medium = $this->getMedium($id);
        $mediaPool = $this->get("miky.media.pool");
        try {
            $provider = $mediaPool->getProvider($medium->getProviderName());
        } catch (\RuntimeException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        } catch (\InvalidArgumentException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        }

        return $this->handleWriteMedium($request, $medium, $provider);
    }

    /**
     * Adds a medium of given provider
     * If you need to upload a file (depends on the provider) you will need to do so by sending content as a multipart/form-data HTTP Request
     * See documentation for more details.
     *
     * @ApiDoc(
     *  resource=true,
     *  input={"class"="miky_media_api_form_media", "name"="", "groups"={"sonata_api_write"}},
     *  output={"class"="Miky\Bundle\MediaBundle\Model\Media", "groups"={"miky_api_read"}},
     *  statusCodes={
     *      200="Returned when successful",
     *      400="Returned when an error has occurred while medium creation",
     *      404="Returned when unable to find medium"
     *  }
     * )
     *
     * @Route(requirements={"provider"="[A-Za-z0-9.]*"})
     *
     * @param string  $provider A media provider
     * @param Request $request  A Symfony request
     *
     * @return MediaInterface
     *
     * @throws NotFoundHttpException
     */
    public function postProviderMediumAction($provider, Request $request)
    {
        $mediaManager = $this->get("miky.media.manager.media");
        $mediaPool = $this->get("miky.media.pool");
        $medium = $mediaManager->create();
        $medium->setProviderName($provider);

        try {
            $mediaProvider = $mediaPool->getProvider($provider);
        } catch (\RuntimeException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        } catch (\InvalidArgumentException $ex) {
            throw new NotFoundHttpException($ex->getMessage(), $ex);
        }

        return $this->handleWriteMedium($request, $medium, $mediaProvider);
    }

    /**
     * Set Binary content for a specific media.
     *
     * @ApiDoc(
     *  input={"class"="Miky\Bundle\MediaBundle\Model\Media", "groups"={"sonata_api_write"}},
     *  output={"class"="Miky\Bundle\MediaBundle\Model\Media", "groups"="miky_api_read"},
     *  statusCodes={
     *      200="Returned when successful",
     *      404="Returned when media is not found"
     *  }
     * )
     *
     * @View(serializerGroups="miky_api_read", serializerEnableMaxDepthChecks=true)
     *
     * @param $id
     * @param Request $request A Symfony request
     *
     * @return MediaInterface
     *
     * @throws NotFoundHttpException
     */
    public function putMediumBinaryContentAction($id, Request $request)
    {
        $mediaManager = $this->get("miky.media.manager.media");

        $media = $this->getMedium($id);

        $media->setBinaryContent($request);

        $mediaManager->save($media);

        return $media;
    }

    /**
     * Retrieves media with id $id or throws an exception if not found.
     *
     * @param int $id
     *
     * @return MediaInterface
     *
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    protected function getMedium($id = null)
    {
        $mediaManager = $this->get("miky.media.manager.media");

        $media = $mediaManager->findOneBy(array('id' => $id));

        if (null === $media) {
            throw new NotFoundHttpException(sprintf('Media (%d) was not found', $id));
        }

        return $media;
    }

    /**
     * Write a medium, this method is used by both POST and PUT action methods.
     *
     * @param Request                $request
     * @param MediaInterface         $media
     * @param MediaProviderInterface $provider
     *
     * @return View|FormInterface
     */
    protected function handleWriteMedium(Request $request, MediaInterface $media, MediaProviderInterface $provider)
    {
        $mediaManager = $this->get("miky.media.manager.media");
        $formFactory = $this->get("form.factory");
        $form = $formFactory->createNamed(null, 'miky_media_api_form_media', $media, array(
            'provider_name' => $provider->getName(),
            'csrf_protection' => false,
        ));

        $form->handleRequest($request);

        if ($form->isValid()) {
            $media = $form->getData();
            $mediaManager->save($media);

            $view = FOSRestView::create($media);


                $context = new Context();
                $context->setGroups(array('miky_api_read'));
                $view->setContext($context);


            return $view;
        }

        return $form;
    }

}
