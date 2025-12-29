# WooCommerce Product Version

A WordPress plugin that adds a version number field to WooCommerce products and exposes it via a REST API endpoint.

## Description

This plugin extends WooCommerce by adding a custom "Version Number" field to product settings. The version information can be accessed via a public REST API endpoint, making it ideal for software products, digital downloads, or any versioned products that need programmatic version checking.

## Features

- Adds a "Version Number" field to the WooCommerce product general settings tab
- Simple text input for version numbers (e.g., 1.0.0, 2.1.5)
- Public REST API endpoint for retrieving product version information
- Returns product name, version, and URL alongside version data
- Lightweight and performant
- No dependencies beyond WordPress and WooCommerce

## Requirements

- WordPress 5.8 or higher
- WooCommerce 3.0 or higher
- PHP 7.4 or higher

## Installation

1. Download the plugin files
2. Upload the `woocommerce-product-version` folder to your `/wp-content/plugins/` directory
3. Activate the plugin through the 'Plugins' menu in WordPress
4. Ensure WooCommerce is installed and activated

## Usage

### Adding Version Numbers to Products

1. Navigate to **Products** in your WordPress admin
2. Edit an existing product or create a new one
3. In the **Product Data** section under the **General** tab, you'll find a new "Version Number" field
4. Enter your product version (e.g., `1.0.0`, `2.1.5`, `3.0.0-beta`)
5. Save the product

### REST API Usage

The plugin provides a public REST API endpoint to retrieve product version information.

#### Endpoint

```
GET /wp-json/product-versioning/v1/products/{id}
```

#### Parameters

- `id` (required): The WooCommerce product ID

#### Example Request

```bash
curl https://your-store.com/wp-json/product-versioning/v1/products/42
```

#### Success Response

**Status Code:** `200 OK`

```json
{
    "status": "success",
    "product_id": 42,
    "product_name": "Pro App Desktop Edition",
    "product_version": "2.1.5",
    "product_url": "https://your-store.com/product/pro-app-desktop-edition/"
}
```

#### Error Responses

**Product Not Found**

**Status Code:** `200 OK`

```json
{
    "status": "error",
    "code": "product_not_found",
    "message": "No WooCommerce product found for the specified product."
}
```

**Missing Product ID**

**Status Code:** `200 OK`

```json
{
    "status": "error",
    "code": "product_id_missing",
    "message": "Invalid Request. No product ID provided."
}
```

## Use Cases

This plugin is perfect for:

- **Software Products**: Allow your application to check for updates by querying the version endpoint
- **Digital Downloads**: Display version information for digital products
- **API Integration**: Integrate product version data into external systems
- **Version Tracking**: Maintain version history for your WooCommerce products

## Example: Version Check in an Application

```python
import requests

def check_for_updates(product_id, current_version):
    url = f"https://your-store.com/wp-json/product-versioning/v1/products/{product_id}"
    response = requests.get(url)
    
    if response.status_code == 200:
        data = response.json()
        if data['status'] == 'success':
            latest_version = data['product_version']
            if latest_version != current_version:
                print(f"Update available: {latest_version}")
                return data['product_url']
    return None
```

## Frequently Asked Questions

### Is this plugin free?

Yes, this plugin is free and open source under the GPL v2 license.

### Does this work with all WooCommerce product types?

Yes, the version field is available for all product types (simple, variable, grouped, etc.).

### Is the API endpoint secure?

The API endpoint is public and read-only. It does not expose sensitive information and cannot be used to modify products.

### Can I customize the API response?

Yes, you can use WordPress filters and hooks to customize the behavior. The plugin code is straightforward and developer-friendly.

## Changelog

### 1.0.0
- Initial release
- Added version number field to products
- REST API endpoint for version retrieval

## Author

**Michael Fisher**
- Email: mfisher31@protonmail.com

## License

This plugin is licensed under the GPL v2 or later.

```
https://www.gnu.org/licenses/gpl-2.0.html
```

## Support

For bug reports and feature requests, please use the GitHub issue tracker.

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.