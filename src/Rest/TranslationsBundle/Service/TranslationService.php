<?php

namespace Kunstmaan\Rest\TranslationsBundle\Service;

use Doctrine\ORM\EntityManager;
use Kunstmaan\Rest\TranslationsBundle\Model\Exception\TranslationException;
use Kunstmaan\TranslatorBundle\Repository\TranslationRepository;
use Kunstmaan\TranslatorBundle\Entity\Translation;
use DateTime;

class TranslationService
{
    const REST = 'REST';
    /** @var EntityManager */
    protected $manager;

    /**
     * @param EntityManager $manager
     */
    public function __construct(EntityManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * @param array $translations
     *
     * @return array
     */
    public function createCollectionFromArray(array $translations)
    {
        $result = [];

        foreach ($translations as $translation) {
            $result[] = $this->createTranslationFromArray($translation);
        }

        return $result;
    }

    /**
     * @param array $translation
     *
     * @return Translation
     *
     * @throws TranslationException
     */
    public function createTranslationFromArray(array $translation)
    {
        if (!$this->validateArrayTranslation($translation)) {
            throw new TranslationException(TranslationException::NOT_VALID);
        }

        $translationEntity = new Translation();

        return $translationEntity
            ->setKeyword($translation['keyword'])
            ->setLocale($translation['locale'])
            ->setText($translation['text'])
            ->setDomain($translation['domain']);
    }

    /**
     * @param Translation $translation
     * @param bool        $force
     *
     * @return null|object
     */
    public function createOrUpdateTranslation(Translation $translation, bool $force = false)
    {
        /** @var TranslationRepository $repository */
        $repository = $this->manager->getRepository(Translation::class);

        $translation->setFile(self::REST);
        /** @var array $result */
        $result = $repository->findBy(['keyword' => $translation->getKeyword(), 'domain' => $translation->getDomain()]);

        /** @var \Kunstmaan\TranslatorBundle\Entity\Translation $oldTrans */
        $oldTrans = array_key_exists(0, $result) ? $result[0] : null;

        if ($force) {
            if ($oldTrans) {
                $model = $oldTrans->getTranslationModel($oldTrans->getId());
                $model->addText($translation->getLocale(), $translation->getText());
                $repository->updateTranslations($model, $oldTrans->getId());
                if ($oldTrans->isDeprecated() || $oldTrans->isDisabled()) {
                    $oldTrans->setStatus(Translation::STATUS_ENABLED);
                }
            } else {
                $repository->createTranslations($translation->getTranslationModel());
            }
        } else {
            if ($oldTrans) {
                if ($oldTrans->getLocale() !== $translation->getLocale()) {
                    $model = $oldTrans->getTranslationModel($oldTrans->getId());
                    $model->addText($translation->getLocale(), $translation->getText());
                    $repository->updateTranslations($model, $oldTrans->getId());
                } elseif ($oldTrans->isDisabled()) {
                    $oldTrans->setStatus(Translation::STATUS_ENABLED);
                    $repository->updateTranslations($translation->getTranslationModel($oldTrans->getId()), $oldTrans->getId());
                } else {
                    return $oldTrans;
                }
            } else {
                $repository->createTranslations($translation->getTranslationModel());
            }
        }

        $this->manager->flush();

        return $repository->findOneBy(['keyword' => $translation->getKeyword(), 'locale' => $translation->getLocale()]);
    }

    /**
     * @param string   $keyword
     * @param string   $domain
     * @param DateTime $date
     */
    public function deprecateTranslations($keyword, $domain)
    {
        /** @var TranslationRepository $repository */
        $repository = $this->manager->getRepository(Translation::class);

        $translations = $repository->findBy(['keyword' => $keyword, 'domain' => $domain]);

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            $translation->setStatus(Translation::STATUS_DEPRECATED);
        }

        $this->manager->flush();
    }

    /**
     * @param DateTime $date
     * @param string   $domain
     */
    public function disableDeprecatedTranslations(DateTime $date, $domain)
    {
        /** @var TranslationRepository $repository */
        $repository = $this->manager->getRepository(Translation::class);

        $translations = $repository->findDeprecatedTranslationsBeforeDate($date, $domain);

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            $translation->setStatus(Translation::STATUS_DISABLED);
        }

        $this->manager->flush();
    }

    /**
     * @param string $keyword
     * @param string $domain
     */
    public function enableDeprecatedTranslations($keyword, $domain)
    {
        /** @var TranslationRepository $repository */
        $repository = $this->manager->getRepository(Translation::class);

        $translations = $repository->findBy(['keyword' => $keyword, 'domain' => $domain]);

        /** @var Translation $translation */
        foreach ($translations as $translation) {
            $translation->setStatus(Translation::STATUS_ENABLED);
        }

        $this->manager->flush();
    }

    /**
     * @param array $translation
     *
     * @return bool
     */
    private function validateArrayTranslation(array $translation)
    {
        return array_key_exists('locale', $translation)
            && array_key_exists('keyword', $translation)
            && array_key_exists('text', $translation)
            && array_key_exists('domain', $translation);
    }
}
