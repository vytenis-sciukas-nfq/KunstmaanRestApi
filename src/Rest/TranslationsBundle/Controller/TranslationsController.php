<?php

namespace Kunstmaan\Rest\TranslationsBundle\Controller;

use DateTime;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\View;
use FOS\RestBundle\Request\ParamFetcherInterface;
use Kunstmaan\Rest\TranslationsBundle\Model\Exception\TranslationException;
use Kunstmaan\Rest\TranslationsBundle\Service\TranslationService;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use Swagger\Annotations as SWG;
use Nelmio\ApiDocBundle\Annotation\Model;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class TranslationsController.
 */
class TranslationsController extends AbstractFOSRestController
{
    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="locale", nullable=false, description="locale")
     * @Rest\Get("/public/translations")
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     *
     *
     * @SWG\Get(
     *     path="/api/public/translations",
     *     description="Get a list of all translations",
     *     operationId="getTranslations",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="locale",
     *         in="query",
     *         type="string",
     *         description="the locale of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/listTranslation")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function getTranslationsAction(ParamFetcherInterface $paramFetcher)
    {
        $locale = $paramFetcher->get('locale');

        if (!$locale) {
            throw new NotFoundHttpException('locale is required');
        }

        $translations = $this->getDoctrine()->getRepository('KunstmaanTranslatorBundle:Translation')
            ->findAllNotDisabled($locale, null);

        return $translations;
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="locale", nullable=false, description="locale")
     * @Rest\Get("/public/translations/{domain}/")
     *
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return array
     *
     *
     * * @SWG\Get(
     *     path="/api/public/translations/{domain}",
     *     description="Get a list of all translations by domain only",
     *     operationId="getTranslationsByDomain",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="locale",
     *         in="query",
     *         type="string",
     *         description="the locale of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         type="string",
     *         description="the domain of the languages you want",
     *         required=false,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/listTranslation")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function getTranslationsByDomainAction(ParamFetcherInterface $paramFetcher, $domain)
    {
        $locale = $paramFetcher->get('locale');

        if (!$locale) {
            throw new NotFoundHttpException('locale is required');
        }

        $translations = $this->getDoctrine()->getRepository('KunstmaanTranslatorBundle:Translation')
            ->findAllNotDisabled($locale, $domain);

        return $translations;
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="locale", nullable=false, description="locale")
     * @Rest\Get("/public/translations/{domain}/{keyword}")
     *
     * @param string                $keyword
     * @param ParamFetcherInterface $paramFetcher
     *
     * @return Translation
     *
     *
     * @SWG\Get(
     *     path="/api/public/translations/{domain}/{keyword}",
     *     description="Get a translation",
     *     operationId="getTranslation",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="locale",
     *         in="query",
     *         type="string",
     *         description="the locale of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="keyword",
     *         in="path",
     *         type="string",
     *         description="the keyword of the translation you want",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         type="string",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Returned when successful",
     *         @SWG\Schema(ref="#/definitions/singleTranslation")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function getTranslationAction($domain, $keyword, ParamFetcherInterface $paramFetcher)
    {
        $locale = $paramFetcher->get('locale');

        if (!$locale) {
            throw new NotFoundHttpException('locale is required');
        }

        /** @var Translation $translation */
        $translation = $this->getDoctrine()
            ->getRepository(Translation::class)
            ->findOneBy(['locale' => $locale, 'keyword' => $keyword, 'domain' => $domain]);

        if ($translation && !$translation->isDisabled()) {
            return $translation;
        }

        throw new NotFoundHttpException();
    }

    /**
     * @View(
     *     statusCode=200
     * )
     *
     * @Rest\QueryParam(name="force", nullable=false, description="Force=true will overwrite existing translations, otherwise will be skipped")
     * @Rest\Post("/translations/{domain}")
     *
     * @param Request $request
     *
     * @return array
     *
     *
     * @SWG\Post(
     *     path="/api/translations/{domain}",
     *     description="Create multiple translations",
     *     operationId="createTranslation",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="translation",
     *         in="body",
     *         required=true,
     *         description="The posted translations",
     *         @SWG\Schema(ref="#/definitions/postTranslations"),
     *     ),
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         type="string",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="force",
     *         in="query",
     *         type="boolean",
     *         description="Force=true will overwrite existing translations, otherwise will be skipped",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Returned when successfully created",
     *         @SWG\Schema(ref="#/definitions/listTranslation")
     *     ),
     *     @SWG\Response(
     *         response=404,
     *         description="Returned when no translations are provided",
     *         @SWG\Schema(ref="#/definitions/ErrorModel")
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function postTranslationsAction(Request $request, ParamFetcherInterface $paramFetcher, $domain = 'messages')
    {
        $force = $paramFetcher->get('force') === "true" ? true : false ;

        /** @var TranslationService $translationCreator */
        $translationCreator = $this->get(TranslationService::class);
        $json = $request->getContent();
        $translations = json_decode($json, true);

        foreach($translations as $key => $translation){
            $translations[$key]['domain'] = $domain;
        }

        $output = [];
        $translations = $translationCreator->createCollectionFromArray($translations);
        foreach ($translations as $translation) {
            $output[] = $translationCreator->createOrUpdateTranslation($translation, $force);
        }

        return $output;
    }

    /**
     * @View(
     *     statusCode=201
     * )
     *
     * @Rest\Put("/translations/deprecate/{domain}")
     *
     * @SWG\Put(
     *     path="/api/translations/deprecate/{domain}",
     *     description="deprecate translations by keyword",
     *     operationId="deprecateTranslation",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="deprecatedTranslation",
     *         in="body",
     *         required=true,
     *         description="The posted translations",
     *         @SWG\Schema(ref="#/definitions/keywordCollection"),
     *     ),
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         type="string",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Returned when successfully deprecated"
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function deprecateTranslationsAction(Request $request, $domain = 'messages')
    {
        /** @var TranslationService $translationCreator */
        $translationCreator = $this->get(TranslationService::class);

        $json = $request->getContent();
        $keywords = json_decode($json, true);

        foreach ($keywords as $keyword) {
            if (!array_key_exists('keyword', $keyword)) {
                throw new TranslationException(TranslationException::NOT_VALID);
            }
        }

        foreach ($keywords as $keyword) {
            $translationCreator->deprecateTranslations($keyword['keyword'], $domain);
        }
    }

    /**
     * @View(
     *     statusCode=201
     * )
     *
     * @Rest\Put("/translations/disable/{domain}")
     *
     * @SWG\Put(
     *     path="/api/translations/disable/{domain}",
     *     description="disable translations by keyword",
     *     operationId="disableTranslation",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="disabledTranslation",
     *         in="body",
     *         required=true,
     *         description="The posted translations",
     *         @SWG\Schema(ref="#/definitions/disablingDate"),
     *     ),
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         type="string",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Returned when successfully disabled"
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function disableDeprecatedTranslationsAction(Request $request, $domain)
    {
        /** @var TranslationService $translationCreator */
        $translationCreator = $this->get(TranslationService::class);

        $json = $request->getContent();
        $translationDeprecation = json_decode($json, true);

        if (!array_key_exists('date', $translationDeprecation)) {
            throw new TranslationException(TranslationException::NOT_VALID);
        }

        $translationCreator->disableDeprecatedTranslations(new DateTime($translationDeprecation['date']), $domain);
    }

    /**
     * @param Request $request
     *
     * @throws TranslationException
     *
     *
     * @Rest\Put("/translations/enable/{domain}")
     *
     * @SWG\Put(
     *     path="/api/translations/enable/{domain}",
     *     description="re-enable translations by keyword",
     *     operationId="enableTranslation",
     *     produces={"application/json"},
     *     tags={"translations"},
     *     @SWG\Parameter(
     *         name="enabledTranslation",
     *         in="body",
     *         required=true,
     *         type="single",
     *         description="The posted translations",
     *         @SWG\Schema(ref="#/definitions/keywordCollection"),
     *     ),
     *     @SWG\Parameter(
     *         name="domain",
     *         in="path",
     *         type="string",
     *         description="the domain of the languages you want",
     *         required=true,
     *     ),
     *     @SWG\Parameter(
     *         name="X-Api-Key",
     *         in="header",
     *         type="string",
     *         description="The authentication access token",
     *         required=true,
     *     ),
     *     @SWG\Response(
     *         response=201,
     *         description="Returned when successfully enabled"
     *     ),
     *     @SWG\Response(
     *         response="default",
     *         description="unexpected error",
     *         @SWG\Schema(
     *             ref="#/definitions/ErrorModel"
     *         )
     *     )
     * )
     */
    public function enableDeprecatedTranslationsAction(Request $request, $domain = 'messages')
    {
        /** @var TranslationService $translationCreator */
        $translationCreator = $this->get(TranslationService::class);

        $json = $request->getContent();
        $keywords = json_decode($json, true);

        foreach ($keywords as $keyword) {
            if (!array_key_exists('keyword', $keyword)) {
                throw new TranslationException(TranslationException::NOT_VALID);
            }
        }

        foreach ($keywords as $keyword) {
            $translationCreator->enableDeprecatedTranslations($keyword['keyword'], $domain);
        }
    }
}
