<?php

namespace Pyz\Yves\Product3DViewerWidget\Widget;

use Generated\Shared\Transfer\Product3DViewTransfer;
use Spryker\Yves\Kernel\Widget\AbstractWidget;

class Product3DViewerWidget extends AbstractWidget
{

    public function __construct(Product3DViewTransfer $product3DViewTransfer)
    {
        $this->addModelUrl($product3DViewTransfer->getModelUrl());
    }

    public static function getName(): string
    {
        return 'product3DViewerWidget';
    }

    public static function getTemplate(): string
    {
        return '@Product3DViewerWidget/views/product-3d-viewer.twig';
    }

    /**
     * @param string $modelUrl
     *
     * @return void
     */
    public function addModelUrl(string $modelUrl): void
    {
        $this->addParameter('modelUrl', $modelUrl);
    }
}
