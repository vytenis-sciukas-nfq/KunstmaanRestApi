<?php

namespace Kunstmaan\Rest\CoreBundle\Entity;

interface HasApiKeyInterface
{
    public function getApiKey();

    public function setApiKey($key);
}