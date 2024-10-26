## Bonus Plus WordPress Plugin

This plugin integrates WordPress/WooCommerce with the BonusPlus loyalty program. It offers a range of features to manage customer loyalty and bonus points.

### Inputs

*   **API Key:**  A unique key obtained from the BonusPlus platform to authorize API requests.
*   **Shop Name:** The name of your store as registered on the BonusPlus platform.
*   **Widget Texts:** Customizable messages for identified, unidentified, and unverified users displayed on the bonus card widget.
*   **Widget Links:**  URLs to redirect different types of users (identified, unidentified, unverified) to specific pages on your site (e.g., shop, registration, account settings).
*   **Product and Category Data:**  The plugin retrieves product and category information from WooCommerce to export to BonusPlus.
*   **Customer Data:**  The plugin uses the customer's billing phone number from WooCommerce to fetch and update bonus data from BonusPlus.
*   **Order Data:** The plugin utilizes order details like product IDs, quantities, and total amount to calculate and process bonus points.

### Outputs

*   **Bonus Card Widget:** Displays the user's bonus card information (card number, available bonuses, etc.) and QR code.
*   **Loyalty Program Tab:** Adds a new tab in the WooCommerce My Account section to show detailed bonus information.
*   **Bonus Point Calculation:** Calculates bonus points earned or redeemed based on product purchases and displays them on product pages, cart, and checkout.
*   **Bonus Point Processing:**  Reserves bonus points when an order is placed and processes the actual earning/redemption when the order status changes (e.g., completed, canceled).
*   **Automatic Customer Registration:** Registers customers in the BonusPlus program upon registration on the WordPress site (if configured).
*   **Export Products and Categories:** Exports the WooCommerce product catalog to BonusPlus for integration.
*   **Logging:** Records errors and key data changes in the WooCommerce logs for debugging.
