# Spryker Product 3D Viewer Widget

[![Latest Version](https://img.shields.io/packagist/v/spryker-community/product-3d-viewer.svg?style=flat-square)](https://packagist.org/packages/spryker-community/product-3d-viewer)
[![MIT Licensed](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build Status](https://img.shields.io/travis/spryker-community/product-3d-viewer/master.svg?style=flat-square)](https://travis-ci.org/spryker-community/product-3d-viewer)

A Spryker Yves module that provides a configurable widget to display interactive 3D models on the Product Detail Page (PDP), leveraging Google's `<model-viewer>` web component.

---

## Overview

In modern e-commerce, providing an immersive and detailed view of products is key to increasing customer engagement and conversion rates. The **Product 3D Viewer Widget** extends the Spryker Commerce OS by allowing you to easily embed interactive 3D models for your products.

This module introduces a new Yves widget that can be placed anywhere in your Twig templates, but is primarily designed for the PDP. It is configured via a Transfer Object, allowing for a clean separation between business logic and presentation, in line with Spryker's architecture.

## Key Features

* **Interactive 3D Models:** Utilizes Google's powerful `<model-viewer>` library for a smooth, interactive 3D experience right in the browser.
* **Augmented Reality (AR) Support:** Includes a one-click "View in your space" button on supported mobile devices to view the product in AR.
* **Lazy Loading:** Models are loaded on-demand when the user clicks a "Load 3D Model" button, ensuring optimal page load performance.
* **Highly Configurable:** Control the viewer's appearance and behavior (dimensions, auto-rotation, poster image) directly from your controller.
* **Enterprise Ready:** Follows Spryker best practices for modularity, separation of concerns, and ease of integration.
* **Atomic Design:** Comes with a pre-built molecule that can be easily customized to fit your project's specific design system.

## Installation

Follow these steps to install and configure the module in your Spryker project.

### 1. Install via Composer

```bash
composer require spryker-community/3d-product-visualizer
```

### 2. Add Core Namespace

Register the `SprykerCommunity` namespace in your project's configuration to allow the Spryker kernel to locate the module's classes.

**File:** `config/Shared/config_default.php`
```php
<?php

use Spryker\Shared\Kernel\KernelConstants;

// ...
$config[KernelConstants::CORE_NAMESPACES] = [
    'Spryker',
    'SprykerCommunity', // Add this line
];
```
### 3. Register your widget in the ShopApplication Dependency Provider

Register the `Product3DViewerWidget` in the Shop Application Dependency provider `src/Pyz/Yves/ShopApplication/ShopApplicationDependencyProvider.php`

**File:** `src/Pyz/Yves/ShopApplication/ShopApplicationDependencyProvider.php`
```php
<?php

use SprykerCommunity\Yves\Product3DViewerWidget\Widget\Product3DViewerWidget;

class ShopApplicationDependencyProvider extends SprykerShopApplicationDependencyProvider
{
    /**
     * @return array<string>
     */
    protected function getGlobalWidgets(): array
    {
        return [
        // ... other widgets
        Product3DViewerWidget::class,
        ]
}
```

### 4. Generate Transfer Objects

The module comes with a transfer definition. You must regenerate the transfer classes to make `Product3DViewTransfer` available throughout the application.

```bash
vendor/bin/console transfer:generate
```

## Usage Guide

### Prerequisite: Create a Product Attribute

Before you can use the module, you must have a product attribute in your system to store the URL of the 3D model.

1.  In the Zed UI, navigate to `Products` > `Product Attributes`.
2.  Create a new text attribute with a key, for example, `model_3d_url`.
3.  For a product, populate this attribute with the publicly accessible URL to your `.glb` or `.gltf` 3D model file. (See Best Practices for hosting recommendations).

### 1. Extend the Controller

You need to provide the data for the 3D viewer from the controller that renders your Product Detail Page. This typically involves extending the `ProductController`.

**File:** `src/Pyz/Yves/ProductDetailPage/Controller/ProductController.php`

```php
<?php

namespace Pyz\Yves\ProductDetailPage\Controller;

use Generated\Shared\Transfer\Product3DViewTransfer;
use SprykerShop\Yves\ProductDetailPage\Controller\ProductController as SprykerProductController;

class ProductController extends SprykerProductController
{
    /**
     * @param array $productData
     *
     * @return array
     */
    protected function executeDetailAction(array $productData, Request $request): array
    {
        $viewData = parent::executeDetailAction($productData, $request);

        $quoteTransfer = new QuoteTransfer();
        $quoteTransfer->addItem(
            (new ItemTransfer())->setIdProductAbstract($viewData['product']->getIdProductAbstract()),
        );
        $modelUrl = $viewData['product']->getAttributes()['model_3d_url'] ?? null;

        if ($modelUrl) {
            // Create and populate the transfer object
            $product3DViewTransfer = (new Product3DViewTransfer())
                ->setModelUrl($modelUrl)
                ->setAltText($viewData['product']->getName() . ' 3D Model') // Example alt text
                ->setEnableAr(true); // Enable AR functionality

            // Add the transfer object to the page data
            $viewData['product3DView'] = $product3DViewTransfer;
        }
        $viewData['cart'] = $quoteTransfer;

        return $viewData;
    }
```

### 2. Integrate the Widget into Twig

Finally, call the widget from your PDP Twig template, passing it the transfer object you just created in the controller.

**File:** `src/Pyz/Yves/ProductDetailPage/Theme/default/views/pdp/pdp.twig` (or your theme's equivalent)

```twig
{# Find a suitable location, for example, near the product image gallery. #}

{% if product3DView is defined and product3DView is not null %}
    {{ widget(
        'Product3DViewerWidget',
        { 'Product3DViewTransfer': product3DView }
    ) }}
{% endif %}
```
> **Note:** The key for the transfer object in the options array (`Product3DViewTransfer`) must exactly match the class name with its namespace.

## Configuration

The widget's behavior is configured entirely through the `Product3DViewTransfer` object. The following properties are available:

| Property               | Type    | Default   | Description                                                                    |
| ---------------------- | ------- | --------- | ------------------------------------------------------------------------------ |
| `modelUrl`             | `string`| (none)    | **Required.** The public URL to your `.glb` or `.gltf` file.                      |
| `altText`              | `string`| (none)    | The `alt` text for the viewer, crucial for accessibility.                        |
| `posterUrl`            | `string`| (none)    | URL to an image shown before the model is loaded.                                |
| `width`                | `string`| `'100%'`  | The CSS width of the viewer component.                                         |
| `height`               | `string`| `'400px'` | The CSS height of the viewer component.                                        |
| `enableAr`             | `bool`  | `true`    | If `true`, shows the "View in your space" button on AR-capable devices.            |
| `enableAutoRotate`     | `bool`  | `false`   | If `true`, the model will automatically rotate when loaded.                        |
| `enableCameraControls` | `bool`  | `true`    | If `true`, allows the user to pan, zoom, and rotate the model with the mouse/touch. |

## Best Practices & Performance

### 3D Model Optimization

For the best user experience, it is critical to optimize your 3D models.
* **Use `.glb` format:** This format embeds all textures and data into a single binary file, simplifying hosting.
* **Use Draco Compression:** Draco is a library for compressing and decompressing 3D geometric meshes and point clouds. It can drastically reduce the size of your model files. Ensure your models are exported with Draco compression enabled.

### Asset Hosting (Cloud Architecture)

Do not host large 3D model files directly on your web server. For a scalable and high-performance solution, use a cloud-based object storage service and a CDN.

* **Recommended Stack:** **AWS S3 + AWS CloudFront**.
    1.  Store your `.glb` files in an S3 bucket.
    2.  Serve the files through a CloudFront distribution.
    3.  Use the CloudFront URL as the value for your `model_3d_url` product attribute.

This setup provides a highly available, low-latency, and globally distributed network for your assets, ensuring fast model load times for all users.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.
