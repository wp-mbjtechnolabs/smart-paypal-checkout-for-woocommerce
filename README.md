# Smart PayPal Checkout For WooCommerce 

PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience

### Description
PayPal Checkout with Smart Payment Buttons gives your buyers a simplified and secure checkout experience. PayPal intelligently presents the most relevant payment types to your shoppers, automatically, making it easier for them to complete their purchase using methods like Pay with Venmo, PayPal Credit, credit card payments, iDEAL, Bancontact, Sofort, and other payment types.

### Introduction 

Easily add PayPal payment options to your WordPress / WooCommerce website.

 * PayPal Checkout (Smart Payment Buttons)
 * Advanced credit and debit card payments ( with 3D Secure for If you are based in Europe, you are subjected to PSD2. PayPal recommends this option )

###  Set each option according to your needs 


#### Basic Fields

- **Enable/Disable** : Check this box to enable the payment gateway. Leave unchecked to disable it.

- **Title** : This controls the label the user will see for this payment option during checkout.

- **Description** : This controls the description the user will see for this payment option during checkout.

- **PayPal sandbox** : Check this box to enable test mode so that all transactions will hit PayPal sandbox server instead of the live server. This should only be used during development as no real transactions will occur when this is enabled. 

#### API credentials : <a target='_blank' href='https://developer.paypal.com/docs/business/get-started/#step-1-get-api-credentials'>Get API credentials</a>

- **PayPal Client ID** : Use your PayPal Client ID.

- **PayPal Secret**:  Use your PayPal Secret.

- **Sandbox Client ID** : Enter your PayPal Sandbox Client ID.

- **Sandbox Secret** : Enter your PayPal Sandbox Secret.

#### Smart Payment Buttons options

- **Button Color** : Set the color you would like to use for the PayPal button.

- **Button Shape** : Set the shape you would like to use for the buttons.

- **Button Label** : Set the label type you would like to use for the PayPal button.

- **Disable funding** : Funding methods selected here will be hidden from showing in the Smart Payment Buttons.

#### Order Review Page options

- **Page Title** : Set the Page Title value you would like used on the PayPal Checkout order review page.

- **Description** : Set the Description you would like used on the PayPal Checkout order review page.

- **Button Text** : Set the Button Text you would like used on the PayPal Checkout order review page.

#### Advanced options

- **Brand Name** : This controls what users see as the brand / company name on PayPal review pages.

- **Landing Page** : The type of landing page to show on the PayPal site for customer checkout. PayPal Account Optional must be checked for this option to be used.

- **Enable/Disable** : Enable advanced credit and debit card payments

- **3D Secure** : If you are based in Europe, you are subjected to PSD2. PayPal recommends this option

- **Payment action** : Choose whether you wish to capture funds immediately or authorize payment only.

- **Invoice prefix** : Please enter a prefix for your invoice numbers. If you use your PayPal account for multiple stores ensure this prefix is unique as PayPal will not allow orders with the same invoice number.

- **Debug log** : Log PayPal events, such as Webhook, Payment, Refund inside text file. Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.


### Advanced credit and debit country and currency availability

Currently PayPal support Unbranded payments in US, AU, UK, FR, IT and ES only.

### Enable your business account for advanced credit and debit card payments

<a target="_blank" href="https://www.sandbox.paypal.com/bizsignup/entry/product/ppcp">Enable for Sandbox Account</a> <span> | </span> <a target="_blank" href="https://www.paypal.com/bizsignup/entry/product/ppcp">Enable for Live Account</a>


### Quality Control 
Payment processing can't go wrong.  It's as simple as that.  Our certified PayPal engineers have developed and thoroughly tested this plugin on the PayPal sandbox (test) servers to ensure your customers don't have problems paying you.  

### Seamless PayPal Integration 
Stop bouncing back and forth between WooCommerce and PayPal to manage and reconcile orders.  We've made sure to include all WooCommerce order data in PayPal transactions so that everything matches in both places.  If you're looking at a PayPal transaction details page it will have all of the same data as a WooCommerce order page, and vice-versa.  

### Get Involved 
Developers can contribute to the source code on the [Smart PayPal Checkout For WooCommerce GitHub repository](https://github.com/wp-mbjtechnolabs/smart-paypal-checkout-for-woocommerce).


### Installation 

#### Minimum Requirements 

* WooCommerce 3.0 or higher

#### Manual Installation

1. Unzip the files and upload the folder into your plugins folder (/wp-content/plugins/) overwriting older versions if they exist
2. Activate the plugin in your WordPress admin area.

 
### Usage 

1. Open the settings page for WooCommerce and click the "Payments" tab
2. Click on the sub-item for PayPal Checkout.
3. Enter your API credentials and adjust any other settings to suit your needs. 

### Updating 

Automatic updates should work great for you.  As always, though, we recommend backing up your site prior to making any updates just to be sure nothing goes wrong.
 
### Frequently Asked Questions

#### How do I create sandbox accounts for testing? 

* Login at http://developer.paypal.com.  
* Click the Applications tab in the top menu.
* Click Sandbox Accounts in the left sidebar menu.
* Click the Create Account button to create a new sandbox account.

#### How to Receive local payments with Smart PayPal Checkout For WooCommerce For PayPal indian merchants?

* You can now start receiving Domestic Payments in INR with your existing PayPal integration by completing 3 steps as mentioned below. Please note that your existing Cross Border Payments remain unaffected by performing the below steps–

1. Complete Domestic KYC :  As per Indian Regulations, you need to complete Domestic KYC to start receiving domestic payments in INR. You can complete Domestic KYC by clicking <a href="https://www.paypal.com/policy/hub/kyc">here</a> and uploading required documents in your PayPal account.  To know more about KYC requirements and the type of documents to be uploaded, please click <a href="https://www.paypal.com/in/webapps/mpp/know-your-customer">here</a>. 
1. Changes to your existing WooCommerce Shopping Cart : If you already have another WooCommerce store to accept domestic payments (INR), steps : Sign in to your admin Panel ->  Go to Settings under WooCommerce -> Choose your base location and set currency as “Indian Rupee”
1. PayPal currently supports INR (Indian Rupee) as transaction currency ONLY for checkout performed by consumers in India (Indian PayPal Accounts). A consumer with PayPal account issued outside of India will be unable to transact in INR via PayPal. Merchants/ Partners are advised that they present product prices to Indian consumers in INR and to consumers outside India in currencies like USD, EUR, GBP, AUD, etc. This is critical to provide optimum user experience to consumer flows and reduce declines on this account. 
    
 
### Upgrade Notice 

#### 1.0.0
After updating, make sure to clear any caching / CDN plugins you may be using.  Also, go into the plugin's gateway settings, review everything, and click Save even if you do not make any changes.
