# Magento 2 Better Tier Price GraphQL/PWA

**Magento 2 Better Tier Price GraphQL is now a part of the Mageplaza Better Tier Price extension that adds GraphQL features. This upgrade enables PWA compatibility, which is incredibly helpful for your Magento 2 stores relating to future updates.** 

[Mageplaza Better Tier Price for Magento 2](https://www.mageplaza.com/magento-2-better-tier-price/) eliminates the limitations of Magento 2 Default that doesn’t allow you to create multiple tier prices, limit the price form of tier prices, and the flexibility to apply tier prices to specific customers. 

Mageplaza Better Tier Price makes life easier for both you and your customers. From the admin backend, you can set up different forms of prices, including fixed prices, fixed discount amount, and discount percent. It’s no longer difficult for the store owners to manage, compare the discount prices and other discount related details.  

There will be an automated tier pricing table that assists your customers to shop with ease and efficiency on your store. Your customers will love this intuitive table because it provides useful information for their purchases, including quantity, tier price per item, and the amount saved by the discount percent. The noticeable difference between this extension and the Magento default is the automation it offers. Accordingly, the quantity of the products will automatically change, conforming to the price per item customers choose and it displays right on the table instead of manually entering the item quantity. 

Another outstanding benefit of this module is that you can create multiple tier groups from the admin backend and apply to the different groups of customers for different purposes. For example, in the Holiday shopping season, with different promotion campaigns, you can create different tier prices for different products to increase the price diversity. You can apply a tier price for the same products in bulk and change to another tier price for all of those products at once. 

To treat your customers in a more personalized way, you can align special tier prices to specific customers and let only them use the exceptional offers. This is a helpful way to increase personalization in customer experience and retain loyalty and repeat customers effectively. 

Mageplaza Tier Price for Magento 2 is a useful tool to create more pricing and discount options for customers when shopping in your store. That’s what makes your store functional and flexible.

## 1. How to install

Run the following command in Magento 2 root folder:

```
composer require mageplaza/module-better-tier-price-graphql
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

**Note:**
Magento 2 Better Tier Price GraphQL requires installing [Mageplaza Better Tier Price](https://www.mageplaza.com/magento-2-better-tier-price/) in your Magento installation.

## 2. How to use

To perform GraphQL queries in Magento, please do the following requirements:

- Use Magento 2.3.x or higher. Set your site to [developer mode](https://www.mageplaza.com/devdocs/enable-disable-developer-mode-magento-2.html).
- Set GraphQL endpoint as `http://<magento2-server>/graphql` in url box, click **Set endpoint**. 
(e.g. `http://dev.site.com/graphql`)
- To view the queries that the **Mageplaza Better Tier Price GraphQL** extension supports, you can look in `Docs > Query` in the right corner

## 3. Devdocs

- [Magento 2 Better Tier Price Rest API & examples](https://documenter.getpostman.com/view/10589000/T1LFpApq)
- [Magento 2 Better Tier Price GraphQL & examples](https://documenter.getpostman.com/view/10589000/TVetb69F)

## 4. Contribute to this module

Feel free to **Fork** and contribute to this module. 
You can create a pull request so we will merge your changes main branch.

## 5. Get Support

- Feel free to [contact us](https://www.mageplaza.com/contact.html) if you have any further questions.
- Like this project, Give us a **Star** ![star](https://i.imgur.com/S8e0ctO.png)
