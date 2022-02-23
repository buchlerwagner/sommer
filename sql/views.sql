# noinspection

-- daily_orders
SELECT
    cart_shop_id AS shopId,
    cat_id AS categoryId,
    cat_title AS categoryName,
    prod_id AS productId,
    prod_name AS productName,
    IFNULL(pv_id, 0) AS variantId,
    pv_name AS variantName,
    citem_quantity AS quantity,
    citem_pack_unit AS unit,

    cart_store_id AS orderOrigin,
    sm_type AS shippingType,
    sm_code AS shippingCode,
    st_name AS shippingStoreName,
    cart_shipping_date AS shippingDate

FROM cart_items
         LEFT JOIN cart ON (cart_id = citem_cart_id)
         LEFT JOIN products ON (prod_id = citem_prod_id)
         LEFT JOIN product_variants ON (pv_id = citem_prod_variant)
         LEFT JOIN product_categories ON (cat_id = prod_cat_id)
         LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)
         LEFT JOIN stores ON (st_code = sm_code)

WHERE cart_status = 'ORDERED' AND (cart_order_status = 'NEW' OR cart_order_status = 'PROCESSING')

ORDER BY cat_id, prod_id;


-- list orders

SELECT
    cart_id AS cartId,
    cart_order_number AS orderNumber,
    cart_shop_id AS shopId,
    cat_id AS categoryId,
    cat_title AS categoryName,
    prod_id AS productId,
    prod_name AS productName,
    IFNULL(pv_id, 0) AS variantId,
    pv_name AS variantName,
    citem_quantity AS quantity,
    citem_pack_unit AS unit,

    cart_store_id AS orderOrigin,
    sm_type AS shippingType,
    sm_code AS shippingCode,
    st_name AS shippingStoreName,
    cart_shipping_date AS shippingDate,
    cart_ordered AS orderDate,
    cart_remarks AS remarks,
    CONCAT(us_lastname, ' ', us_firstname) AS customerName,
    us_phone AS customerPhone

FROM cart_items
         LEFT JOIN cart ON (cart_id = citem_cart_id)
         LEFT JOIN products ON (prod_id = citem_prod_id)
         LEFT JOIN product_variants ON (pv_id = citem_prod_variant)
         LEFT JOIN product_categories ON (cat_id = prod_cat_id)
         LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)
         LEFT JOIN stores ON (st_code = sm_code)
         LEFT JOIN users ON (us_id = cart_us_id)

WHERE cart_status = 'ORDERED' AND (cart_order_status = 'NEW' OR cart_order_status = 'PROCESSING')

ORDER BY cart_ordered, cart_id;


-- daily sale
SELECT
    cart_id AS cartId,
    cart_order_number AS orderNumber,
    cart_order_status AS orderStatus,
    cart_shop_id AS shopId,
    cat_id AS categoryId,
    cat_title AS categoryName,
    prod_id AS productId,
    prod_name AS productName,
    IFNULL(pv_id, 0) AS variantId,
    pv_name AS variantName,
    citem_quantity AS quantity,
    citem_pack_unit AS unit,
    citem_local_consumption AS isLocalConsumption,
    IF(citem_local_consumption, IFNULL(pv_vat_local, prod_vat_local), IFNULL(pv_vat_deliver, prod_vat_deliver)) AS vatKey,
    ROUND((citem_price * citem_quantity) * (IF(citem_local_consumption, IFNULL(pv_vat_local, prod_vat_local), IFNULL(pv_vat_deliver, prod_vat_deliver)) / 100)) AS vat,
    ROUND((citem_price * citem_quantity) / (IF(citem_local_consumption, IFNULL(pv_vat_local, prod_vat_local), IFNULL(pv_vat_deliver, prod_vat_deliver)) / 100 + 1)) AS netTotal,
    (citem_price * citem_quantity) AS grossTotal,
    cart_currency AS currency,

    cart_store_id AS orderOrigin,
    s2.st_name AS originStoreName,
    sm_type AS shippingType,
    sm_code AS shippingCode,
    s1.st_name AS shippingStoreName,
    cart_us_id AS sellerId,
    cart_ordered AS orderDate,
    cart_paid AS isPaid

FROM cart_items
         LEFT JOIN cart ON (cart_id = citem_cart_id)
         LEFT JOIN products ON (prod_id = citem_prod_id)
         LEFT JOIN product_variants ON (pv_id = citem_prod_variant)
         LEFT JOIN product_categories ON (cat_id = prod_cat_id)
         LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)
         LEFT JOIN stores AS s1 ON (s1.st_code = sm_code)
         LEFT JOIN stores AS s2 ON (s2.st_code = cart_store_id)

WHERE cart_status = 'ORDERED'

ORDER BY cart_ordered;

-- not good for view:
SELECT
    cart_id AS cartId,
    cart_order_number AS orderNumber,
    cart_order_status AS orderStatus,
    cart_shop_id AS shopId,
    cat_id AS categoryId,
    cat_title AS categoryName,
    prod_id AS productId,
    prod_name AS productName,
    IFNULL(pv_id, 0) AS variantId,
    pv_name AS variantName,
    citem_quantity AS quantity,
    citem_pack_unit AS unit,
    citem_local_consumption AS isLocalConsumption,
    @vatKey := IF(citem_local_consumption, IFNULL(pv_vat_local, prod_vat_local), IFNULL(pv_vat_deliver, prod_vat_deliver)) AS vatKey,
    @vat := @vatKey / 100 AS vat,
    (citem_price * citem_quantity) AS grossTotal,
    ROUND((citem_price * citem_quantity) / (@vat + 1)) AS netTotal,

    cart_store_id AS orderOrigin,
    sm_type AS shippingType,
    sm_code AS shippingCode,
    st_name AS shippingStoreName,
    cart_ordered AS orderDate

FROM cart_items
         LEFT JOIN cart ON (cart_id = citem_cart_id)
         LEFT JOIN products ON (prod_id = citem_prod_id)
         LEFT JOIN product_variants ON (pv_id = citem_prod_variant)
         LEFT JOIN product_categories ON (cat_id = prod_cat_id)
         LEFT JOIN shipping_modes ON (sm_id = cart_sm_id)
         LEFT JOIN stores ON (st_code = sm_code)

WHERE cart_status = 'ORDERED'

ORDER BY cart_ordered;