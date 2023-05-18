<?php

namespace App\Models;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Constant;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PimProduct extends Model
{
    use SoftDeletes;

    protected $guarded = [];
    public static $validationRules = [
        'addToCart' => [
            'product_id' => 'required|numeric|exists:pim_products,id',
            'customer_address_id' => 'required|numeric|exists:customer_addresses,id',
        ],
    ];

    public static function getValidationRules( $type, $params = [] )
    {
        $cartAllowedActions = array_key_exists('cart_actions_allowed', $params) ? $params['cart_actions_allowed'] : [];
        $allowedActions = array_key_exists('actions_allowed', $params) ? $params['actions_allowed'] : [];
        $productId = array_key_exists('product_id', $params) ? $params['product_id'] : [];
        $customerId = array_key_exists('customer_id', $params) ? $params['customer_id'] : [];
        $cartId = array_key_exists('cart_id', $params) ? $params['cart_id'] : [];
        $rules = [
            'filters' => [
                'filters' => 'required',
                'filters.price_range' =>  "required",
                'filters.price_range.min' =>  "nullable",
                'filters.price_range.max' =>  "nullable",
                'filters.store_slug' =>  "nullable",
                'filters.sort_by' =>  "required",
                'filters.sort_by.featured' =>  "required",
                'filters.sort_by.newest_arrival' =>  "required",
                'filters.sort_by.price_high_to_low' =>  "required",
                'filters.sort_by.price_low_to_high' =>  "required",
            ],
            'cart' => [
                'action'      => 'required|string|'.Rule::in($cartAllowedActions),
            ],
            'add-cart-item' => [
                'cart_item_id' => 'nullable|numeric|'.Rule::exists('customer_cart_items', 'id')->where('cart_id', $cartId),
                'customer_address_id' => 'required|numeric|exists:customer_addresses,id',
                'product_id' => 'required|numeric|'.Rule::exists('pim_products', 'id'),
                'product_variant_id' => 'required|numeric|'.Rule::exists('pim_product_variant', 'id')->where('product_id', $productId),
                'product_qty' => 'nullable|numeric|gt:0',
                'product_attributes' => 'nullable|array',
                'product_attributes.*.attribute_id' =>  "nullable|integer",
                'product_attributes.*.option_id' =>  "nullable|integer",
                'product_attributes.*.option_value' =>  "nullable|string",
                'listing_type' => 'required|numeric|'.Rule::in(Constant::CUSTOMER_APP_PRODUCT_LISTING),
            ],
            'remove-cart-item' => [
                'cart_item_id' => 'nullable|numeric|'.Rule::exists('customer_cart_items', 'id')->where('cart_id', $cartId),
                'product_id' => 'required|numeric|'.Rule::exists('pim_products', 'id'),
                'product_variant_id' => 'required|numeric|'.Rule::exists('pim_product_variant', 'id')->where('product_id', $productId),
                'product_qty' => 'nullable|numeric|gt:0',
                'listing_type' => 'required|numeric|'.Rule::in(Constant::CUSTOMER_APP_PRODUCT_LISTING),
            ],
            'delete-cart-item' => [
                'cart_item_id' => 'nullable|numeric|'.Rule::exists('customer_cart_items', 'id')->where('cart_id', $cartId),
                'product_id' => 'required|numeric|'.Rule::exists('pim_products', 'id'),
                'product_variant_id' => 'required|numeric|'.Rule::exists('pim_product_variant', 'id')->where('product_id', $productId),
                'listing_type' => 'required|numeric|'.Rule::in(Constant::CUSTOMER_APP_PRODUCT_LISTING)
            ],
            'update-cart' => [
                'customer_address_id' => 'required|numeric|exists:customer_addresses,id',
                'payment_method_id' => 'required|numeric|'.Rule::exists('payment_methods', 'id'),
                'token_id' => 'nullable|numeric',
            ],
            'update-session' => [
                'cart_ref' => 'nullable|string|'.Rule::exists('customer_cart', 'cart_ref')->where('customer_id', $customerId),
            ],
            'product-detail' => [
                'product_id' => 'required|numeric',
                'referrer_type'      => 'nullable|numeric|'.Rule::in($allowedActions),
                'customer_address_id' => 'nullable',
                'listing_type' => 'nullable',
                'slug' => 'nullable'
            ],
        ];

        return $rules[ $type ];
    }


    public function getPriceAttribute($value)
    {
        return ceil($value);
    }

    public function getNameAttribute($value)
    {
        return htmlspecialchars_decode($value);
    }

    public function variants()
    {
        return $this->hasMany(PimProductVariant::class, 'product_id')
            ->with([
                'image:id,url,position',
            ]);
    }

    public function attribute()
    {
        return $this->hasMany(PimProductAttribute::class, 'product_id', 'id');
    }

    public function merchant()
    {
        return $this->belongsTo(Merchant::class, 'merchant_id');
    }

    public function attributeOption()
    {
        return $this->hasMany(PimProductVariantOption::class, 'product_id', 'id');
    }

    public function activeVariants()
    {
        return $this->hasMany(PimProductVariant::class, 'product_id', 'id')
            ->where('is_active', Constant::Yes)
            ->with([
                'image:id,url,position',
            ]);
    }

    public function category()
    {
        return $this->hasMany(PimProductCategory::class, 'product_id', 'id');
    }

    public function productStore()
    {
        return $this->belongsTo(MerchantStore::class, 'store_id', 'id');
    }

    public function store()
    {
        return $this->belongsTo(MerchantStore::class, 'store_id', 'id')->withTrashed();
    }



    public function images()
    {
        return $this->hasMany(PimProductImage::class, 'product_id', 'id')
            ->where('is_active',Constant::Yes)->orderBy('position')->select('id','product_id','url');
    }

    public function defaultImage()
    {
        $defaultImage = PimProductImage::getPlaceholder();
        return $this->hasOne(PimProductImage::class, 'product_id', 'id')
            ->where('is_default',Constant::Yes)
            ->withDefault([
                'id' => $defaultImage->id,
                'product_id' => $defaultImage->product_id,
                'url' => $defaultImage->getAttributes()['url'],
            ]);
    }

    public static function findById($id)
    {
        return self::where('id', $id)->first();
    }

    public static function getById($id)
    {
        return self::where('id', $id)->where('is_active', Constant::Yes)->first();
    }

    public static function getProductImagePlaceholder()
    {
        return env('IMGIX_BASE_PATH') . '/' . env('ENV_FOLDER') . env('UNIVERSAL_CHECKOUT_PRODUCT_IMAGE_PLACEHOLDER');
    }


    public static function getProductDetailForCustomerPortal($productId, $productListingType = 0, $productStoreSlug = '')
    {
        $fields = [
            'pim_products.id as id',
            'name',
            'price',
            'merchant_id',
            'store_id',
            'max_quantity',
            'short_description',
            'pim_products.has_variants as has_variants',
        ];

        $product = self::whereId($productId)
            ->select($fields)
            ->with([
                'defaultImage:id,product_id,url,position',
                'productStore' => function($query) {
                    $query->select([
                        'id',
                        'merchant_id',
                        'store_slug',
                        'name',
                        'is_bshop_enabled',
                        'area_based_shipment'
                    ])->with([
                        'branding'  => function($query) {
                            $query->select([
                                'id',
                                'store_id',
                                'favicon',
                                'website',
                            ]);
                        },
                    ]);
                },
                'attribute' => function($query) {
                    $query->select([
                        'id',
                        'product_id',
                        'attribute_id',
                        'attribute_value',
                    ])
                    ->with(['options:id,product_id,attribute_id,pim_product_attribute_id,option_id,option_value'])
                    ->whereHas('options');
                },
                'attributeOption' => function($query) {
                    $query->select([
                        'id',
                        'product_id',
                        'variant_id',
                        'attribute_id',
                        'option_id'
                    ])->whereHas('variant');
                },
                'activeVariants' => function($query) {
                    $query->where('is_active', Constant::Yes);
                }
            ])
            ->whereHas('merchant', function($query) {
                $query->select('id','is_active','user_id')->where('is_active',Constant::Yes)
                    ->whereHas('user', function ($records)
                    {
                        $records->where("is_active",  Constant::Yes);
                    });
            })
            ->whereHas('activeVariants', function($query) {
                $query->where('is_active', Constant::Yes);
            })
            ->where('pim_products.is_active', Constant::Yes)->first();
        $productStore = "";
        if(!empty($product)) {
            $productStore = $product->store;
            unset($product['store']);

            $campaign = '';


            $defaultImage = PimProductImage::getPlaceholder();
            $product['images'] =  $product->images()->count() == 0 ? [
                [
                    'id' => $defaultImage->id,
                    'product_id' => $defaultImage->product_id,
                    'url' => $defaultImage->url,
                ]
            ] : $product->images;

            $image = optional(optional($product)->defaultImage)->url;
            $product['image'] = !empty($image) ? $image : Helper::getProductImagePlaceholder();
            $product['store'] = [
                'store_name' => $productStore->name,
                'store_slug' => $productStore->store_slug,
                'store_favicon' => $productStore->getStoreAppFavicon(),
                'store_website' => $productStore->getStoreAppFavicon(),
            ];
            $productVariants = $product->activeVariants;
            $variantAttributes = [];
            $attributeOptions = [];
            $availableOptions = [];
            if(!empty($product['attributeOption'])){
                foreach ($product['attributeOption'] as $attributeOption) {
                    if(array_key_exists($attributeOption->attribute_id, $availableOptions)){
                        $availableOptions[$attributeOption->attribute_id][] = $attributeOption->option_id;
                    }else {
                        $availableOptions[$attributeOption->attribute_id] = [$attributeOption->option_id];
                    }
                    $attributeOptions[] = [
                        'variant_id' => $attributeOption->variant_id,
                        'attribute_id' => $attributeOption->attribute_id,
                        'options' => $attributeOption->option_id
                    ];

                    // variant options formatting
                    $variantAttributes[$attributeOption->variant_id][] = [
                        "attribute_id" => $attributeOption->attribute_id,
                        "option_id" => $attributeOption->option_id
                    ];
                }
            }
            $attributes = [];
            if($product->has_variants == Constant::Yes && !empty($product->attribute)){
                foreach ($product->attribute as $productAttribute) {
                    if(array_key_exists($productAttribute->attribute_id, $availableOptions)){
                        $options = [];
                        $optionsAllowed = array_key_exists($productAttribute->attribute_id, $availableOptions) ? $availableOptions[$productAttribute->attribute_id] : [];

                        foreach ($productAttribute['options'] as $option) {
                            if(array_key_exists(0,$availableOptions[$productAttribute->attribute_id])){
                                $options[] = [
                                    'id' => $option->option_id,
                                    'value' => $option->option_value,
                                ];
                            }else {
                                if(in_array($option->option_id, $optionsAllowed)){
                                    $options[] = [
                                        'id' => $option->option_id,
                                        'value' => $option->option_value,
                                    ];
                                }
                            }
                        }
                        $attributes[] = [
                            'id' => $productAttribute->attribute_id,
                            'name' => $productAttribute->attribute_value,
                            'options' => $options
                        ];
                    }
                }
            }
            $productVariantDetails = [];
            $defaultVariantId = '';
            foreach ($productVariants as $productVariant) {
                if(empty($defaultVariantId)){
                    $defaultVariantId = $productVariant->id;
                }
                $productVariantAttributes = [];

                if($product->has_variants == Constant::Yes && !empty($attributes)) {
                    foreach ($variantAttributes[$productVariant->id] as $variantAttribute) {
                        $attrId = $variantAttribute['attribute_id'];
                        $optId = $variantAttribute['option_id'];
                        $attr = array_reduce($attributes, function ($carry, $item) use ($attrId, $optId) {
                            if ($item['id'] == $attrId) {
                                $carry = $item;
                                if ($optId != 0) {
                                    $carry['options'] = array_reduce($item['options'], function ($optCarry, $optItem) use ($attrId, $optId) {
                                        if ($optItem['id'] == $optId) {
                                            $optCarry = $optItem;
                                        }
                                        return $optCarry;
                                    });
                                }
                            }
                            return $carry;
                        });


                        $productVariantAttributes[] = $attr;
                    }
                }

                $discountType = $productVariant->best_discount_type;
                $price = $productVariant->ultimate_best_price;
                $discount = $productVariant->ultimate_discount;
                $discountedPrice = $productVariant->ultimate_best_discounted_price;

                $productVariantDetails[] = (object)[
                    'discount' => $discount,
                    'discount_type' => $discountType,
                    'discounted_price' => $discountedPrice,
                    'image' => !empty($image) ? $productVariant->image->url : Helper::getProductImagePlaceholder(),
                    'track_quantity' => $productVariant->track_quantity,
                    'max_quantity' => $productVariant->track_quantity == Constant::No ? $product->max_quantity : (($productVariant->quantity > $product->max_quantity) ? $product->max_quantity : $productVariant->quantity),
                    'price' => $price,
                    'sku' => $productVariant->sku,
                    'variant_id' => $productVariant->id,
                    'variant_name' => $productVariant->name,
                    'variant_short_description' => $productVariant->short_description,
                    'attributes' => $productVariantAttributes,
                ];
            }
            $product['variants'] = $productVariantDetails;
//            $product['variants'] = $productVariants;
            $HexEqualsTo = "%3D";
            $product['share_link'] = env('UNIVERSAL_PRODUCT_SHARE_DEEPLINK_URL')."productId".$HexEqualsTo."".$product->id;
            $product['variant_ref'] = $attributeOptions;
            $product['default_variant_id'] = $defaultVariantId;
            $product['listing_type'] = $productListingType;
            $product['listing_slug'] = '';
            $product['attributes'] = $attributes;

            unset($product['store_id']);
            unset($product['merchant_id']);
            unset($product['defaultImage']);
            unset($product['attribute']);
            unset($product['attributeOption']);
            unset($product['productStore']);
            unset($product['activeVariants']);
            unset($product['merchant']);
            unset($product['default_shipment_methods']);
        }
        return [
            'product' => $product
        ];
    }

    public static function getProductsForCustomerPortal($listingType = Constant::CUSTOMER_APP_PRODUCT_LISTING['FEATURED_PRODUCTS'], $perPage , $listOptions = [], $disablePagination = false)
    {
        $slug = "";
        $store = array_key_exists('store', $listOptions) ? $listOptions['store'] : [];
        $categoryId = array_key_exists('categoryId', $listOptions) ? $listOptions['categoryId'] : [];
        $campaignId = array_key_exists('campaignId', $listOptions) ? $listOptions['campaignId'] : [];
        $categoryIds = array_key_exists('categoryIds', $listOptions) ? $listOptions['categoryIds'] : [];
        $campaignSlug = array_key_exists('campaignSlug', $listOptions) ? $listOptions['campaignSlug'] : [];
        $bsCategorySlug = array_key_exists('bsCategorySlug', $listOptions) ? $listOptions['bsCategorySlug'] : '';
        $filters = array_key_exists('filters', $listOptions) ? $listOptions['filters'] : '';
        $limitRecord = array_key_exists('limit_record', $listOptions) ? $listOptions['limit_record'] : '';
        $skipRecord = array_key_exists('skip_record', $listOptions) ? $listOptions['skip_record'] : false;
        $skipCount = array_key_exists('skip_count', $listOptions) ? $listOptions['skip_count'] : '';
        $filterByProductIds = array_key_exists('filter_by_product_ids', $listOptions) ? $listOptions['filter_by_product_ids'] : "";
        $campaign = array_key_exists('campaign', $listOptions) ? $listOptions['campaign'] : "";
        $customerId = array_key_exists('customer_id', $listOptions) ? $listOptions['customer_id'] : "";
        $excludedProductId = array_key_exists('exclude_product', $listOptions) ? $listOptions['exclude_product'] : "";
        $storeShipmentDisabled = array_key_exists('store_disabled', $listOptions) ? $listOptions['store_disabled'] : Constant::Yes;
        $storeShipmentError = array_key_exists('shipment_error', $listOptions) ? $listOptions['shipment_error'] : '';

        $fields = [
            'pim_products.id as id',
            'name',
            'price',
            'merchant_id',
            'store_id',
            'max_quantity',
            'short_description',
            'has_variants',
            'featured_position',
            'pim_products.position as position',
            'rank',
        ];

        if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['RECENTLY_VIEWED_PRODUCTS']){
            $fields[] = 'cp_recently_viewed.created_at as recently_viewed_created_at';
        }


        $category = null;
        $productListing = array_flip(Constant::CUSTOMER_APP_PRODUCT_LISTING);
        $type = $productListing[$listingType];

        if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['CATEGORY_PRODUCTS'] && !empty($bsCategorySlug)){
            $slug = $bsCategorySlug;
            $category = PimBsCategory::getCategoryBySlug( $bsCategorySlug );
        }

        $products = self::select($fields);

//        if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['RECENTLY_VIEWED_PRODUCTS']){
//            $products = $products->join('customer_product_recently_viewed as cp_recently_viewed', 'cp_recently_viewed.product_id', '=', 'pim_products.id')
//                        ->where('cp_recently_viewed.customer_id', $customerId)
//                        ->orderBy('cp_recently_viewed.viewed_at', 'DESC');
//            if(!empty($excludedProductId)){
//                $products->where('pim_products.id', '<>', $excludedProductId);
//            }
//        }

        if(!empty($filterByProductIds)){
            $products = $products->whereIn('pim_products.id',$filterByProductIds);
        }

        $products = $products->with([
                'defaultImage:id,product_id,url,position',
                'productStore' => function($query) {
                    $query->select([
                        'id',
                        'merchant_id',
                        'store_slug',
                        'name',
                        'is_bshop_enabled',
                        'plugin_status',
                        'plugin_installed',
                    ])->with([
                        'branding'  => function($query) {
                            $query->select([
                                'id',
                                'store_id',
                                'favicon',
                                'website',
                            ]);
                        },
                    ]);
                },
                'merchant' => function($query) {
                    $query->select([
                        'id',
                    ]);
                },
                'attribute' => function($query) {
                    $query->select([
                        'id',
                        'product_id',
                        'attribute_id',
                        'attribute_value',
                    ])
                    ->with(['options:id,product_id,attribute_id,pim_product_attribute_id,option_id,option_value'])
                    ->whereHas('options');
                },
                'attributeOption' => function($query) {
                    $query->select([
                        'id',
                        'product_id',
                        'variant_id',
                        'attribute_id',
                        'option_id'
                    ])->whereHas('variant');
                },
                "activeVariants" => function($query) {
                    $query->select([
                        'id as variant_id',
                        'product_variant as variant_name',
                        'product_id',
                        'sku',
                        'quantity as max_quantity',
                        'price',
                        'discount',
                        'discount_type',
                        'is_active',
                        'image_id',
                        'track_quantity',
                        'short_description as variant_short_description',
                        'best_discount_value',
                        'best_discount_type',
                    ])->where('is_active', Constant::Yes);

                },
            ])
            ->whereHas('merchant', function($query) {
                $query->select('id','is_active','user_id')->where('is_active',Constant::Yes)
                    ->whereHas('user', function ($records)
                    {
                        $records->where("is_active",  Constant::Yes);
                    });
            })
            ->whereHas('activeVariants', function($query) {
                $query->where('is_active', Constant::Yes);
                $query->where(function($q){
                    $q->where('quantity', '>', 0)
                        ->orWhere('track_quantity',Constant::Yes);
                })->orWhere('track_quantity',Constant::No);
            })
            ->where('pim_products.is_active', Constant::Yes);


        $filteredProducts = $products;

        $filtersApplied = false;
//
//        if(!empty($filters)){
//            if(array_key_exists('store_slug', $filters) && !empty($filters['store_slug'])){
//                $filteredProducts->when($filters, function ($query) use ($filters) {
//                    $_slug = MerchantStore::filterStoreIdsByStoreSlugs($filters['store_slug']);
//                    return $query->whereIn('store_id', $_slug);
//                });
//                $filtersApplied = true;
//            }
//            if(array_key_exists('min', $filters['price_range']) &&  $filters['price_range']['min'] >= 0 && !empty($filters['price_range']['max'])){
//                $filter_min_price = $filters['price_range']['min'];
//                $filter_max_price = $filters['price_range']['max'];
//                $filteredProducts->whereBetween('pim_products.price', [$filter_min_price, $filter_max_price]);
//                $filtersApplied = true;
//            }
//
//            if(array_key_exists('price_high_to_low', $filters['sort_by']) &&  $filters['sort_by']['price_high_to_low'] == 1){
//                $filteredProducts->orderBy('pim_products.price', "DESC");
//                $filtersApplied = true;
//            }
//            else if(array_key_exists('price_low_to_high', $filters['sort_by']) &&  $filters['sort_by']['price_low_to_high'] == 1){
//                $filteredProducts->orderBy('pim_products.price', "ASC");
//                $filtersApplied = true;
//            }
//            else if(array_key_exists('newest_arrival', $filters['sort_by']) &&  $filters['sort_by']['newest_arrival'] == 1){
//                $filteredProducts->orderBy('pim_products.id', "DESC");
//                $filtersApplied = true;
//            }
//            else if(array_key_exists('featured', $filters['sort_by']) &&  $filters['sort_by']['featured'] == 1){
//                $filteredProducts->where('is_featured', Constant::Yes);
//                $filteredProducts->orderBy('featured_position','ASC');
//                $filtersApplied = true;
//            }
//        }

//
//        if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['FEATURED_PRODUCTS']){
//            $products->where('is_featured', Constant::Yes);
//            $products->orderBy('featured_position','ASC');
//
//            if($filtersApplied) {
//                $filteredProducts->where('is_featured', Constant::Yes);
//                $filteredProducts->orderBy('featured_position','ASC');
//            }
//        }
//        else if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['STORES_PRODUCTS'] && !empty($store)){
//            $slug = $store->store_slug;
//            $products->where('pim_products.merchant_id', $store->merchant_id)
//                     ->where('pim_products.store_id', $store->id);
//            if(!empty($categoryId)){
//                $products->whereHas('category', function ($query) use ($categoryId) {
//                    $query->where('category_id',$categoryId);
//                });
//            }
//            $products->orderBy('rank','ASC');
//
//            if($filtersApplied) {
//                $filteredProducts->where('pim_products.merchant_id', $store->merchant_id)
//                    ->where('pim_products.store_id', $store->id);
//                if(!empty($categoryId)){
//                    $filteredProducts->whereHas('category', function ($query) use ($categoryId) {
//                        $query->where('category_id',$categoryId);
//                    });
//                }
//                $filteredProducts->orderBy('rank','ASC');
//            }
//        }
//        else if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['CATEGORY_PRODUCTS'] && !empty($categoryIds)){
//            $products->with([
//                'category' => function($query) use($categoryId, $store) {
//                    $query->select([
//                        'product_id',
//                        'category_id',
//                    ]);
//                },
//            ])->whereHas('category', function ($records) use ($categoryIds)
//            {
//                $records->whereIn('category_id', $categoryIds)->orderBy('id','ASC');
//            });
//            $products->orderBy('rank','ASC');
//
//            if($filtersApplied) {
//                $filteredProducts->with([
//                    'category' => function($query) use($categoryId, $store) {
//                        $query->select([
//                            'product_id',
//                            'category_id',
//                        ]);
//                    },
//                ])->whereHas('category', function ($records) use ($categoryIds)
//                {
//                    $records->whereIn('category_id', $categoryIds)->orderBy('id','ASC');
//                });
//                $filteredProducts->orderBy('rank','ASC');
//            }
//        }
//        else if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['CAMPAIGN_PRODUCTS']){
//            $slug = $campaignSlug;
//
//            $products->orderBy('campaign_product_position','ASC');
//
//            if($filtersApplied) {
//                $filteredProducts->orderBy('campaign_product_position','ASC');
//            }
//
//        }
//        else if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['RECENTLY_VIEWED_PRODUCTS']){
//            $products->orderBy('recently_viewed_created_at','DESC');
//        }


        $productMax = $products->max('price');
        $productMin = $products->min('price');

        // IF $skipRecord is true then use ;
        if($skipRecord) {
            if(!$filtersApplied) {
                $productList = $products->offset($skipCount)->limit($limitRecord)->get();
            }else {
                $productList = $filteredProducts->offset($skipCount)->limit($limitRecord)->get();
            }
            $productsTransformed = $productList;
        }else {
            if(!$filtersApplied) {
                $productList = $products->paginate($perPage);
            }else {
                $productList = $filteredProducts->paginate($perPage);
            }
            $productsTransformed = $productList->getCollection();
        }

        $productsTransformed = $productsTransformed
            ->map(function($item) use($slug, $listingType, $category, $campaignSlug, $campaignId, $filters, $campaign, $storeShipmentDisabled, $storeShipmentError) {
                $defaultVariant = $item->activeVariants->first();
                $discountType = $defaultVariant->ultimate_best_discount_type;
                $price = $defaultVariant->ultimate_best_price;
                $discount = $defaultVariant->ultimate_discount;
                $discountedPrice = $defaultVariant->ultimate_best_discounted_price;
                $productListingType = 0;
                $showSlug = '';
                if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['CATEGORY_PRODUCTS']){
                    $position = $item->rank;
                } else if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['FEATURED_PRODUCTS']){
                    $position = $item->featured_position;
                } else {
                    $position = $item->position;
                }

                $image = optional(optional($item)->defaultImage)->url;
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'listing_type' => $productListingType,
                    'list_type' => $listingType,
                    'listing_slug' => $showSlug ? $campaignSlug : '',
                    'discount' => empty($discount) ? 0 : $discount,
                    'price' => $price,
                    'discounted_price' => $discountedPrice,
                    'discount_badge' => [
                        'show' =>  Constant::Yes,
                        'discount' => $discount,
                        'type' => $discountType,
                    ],
                    'max_quantity' => $defaultVariant->track_quantity == Constant::Yes ? $defaultVariant->max_quantity : $item->max_quantity,
                    'has_variants' => $item->has_variants,
                    'image' => !empty($image) ? $image : Helper::getProductImagePlaceholder(),
                    'position' => $position,
                    'variant_count' => $item->activeVariants->count(),
                    'attribute_count' => $item->attribute->count(),
                    'default_variant_id' => $defaultVariant->variant_id,
                    'category_name' => !empty($category) && !empty($category->parent) ? $category->parent->name : optional($category)->name,
                    'category_id' => !empty($category) && !empty($category->parent) ? $category->parent->id : optional($category)->id,
                    'sub_category_name' => !empty($category) && !empty($category->parent) ? $category->name : null,
                    'sub_category_id' => !empty($category) && !empty($category->parent) ? $category->id : null,
                    'merchant_name' => "My company",
                    'store_name' => $item->productStore->name,
                    'store_slug' => $item->productStore->store_slug,
                    'store_favicon' => $item->productStore->getStoreAppFavicon(),
                    'store_website' => $item->productStore->getStoreAppFavicon(),
                    'store_disabled' => $storeShipmentDisabled,
                    'store_disabled_note' => $storeShipmentError,
                ];
            })
            ->toArray();

//        $filteredStores = $products->groupBy('store_id')->pluck('store_id');
//
//        $stores = MerchantStore::getStoresListing(Constant::CUSTOMER_APP_STORE_LISTING_TYPE['Filters'], null, ['storeIds' => $filteredStores]);

//        if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['STORES_PRODUCTS']){
            $stores = "";
//        }

        if($disablePagination) {
            return $productsTransformed;
        }else {
            $finalResult = new \Illuminate\Pagination\LengthAwarePaginator(
                $productsTransformed,
                $productList->total(),
                $productList->perPage(),
                $productList->currentPage(), [
                    'path' => \Request::url(),
                    'query' => [
                        'page' => $productList->currentPage(),
                    ]
                ]
            );
            $productResults = [
                'products' => $finalResult,
                'type' => $type,
                'slug' => $slug,
                'filters' => []
            ];
            if(!$filtersApplied) {
                $sortByFilters = Constant::SORT_BY_FILTERS;
                if($listingType == Constant::CUSTOMER_APP_PRODUCT_LISTING['FEATURED_PRODUCTS']){
                    unset($sortByFilters['featured']);
                }
                $productResults['slug'] = $slug;
                $productResults['filters'] = [
                    'sort_by' => $sortByFilters,
                    'price_range' => [
                        'max' => $productMax,
                        'min' => $productMin,
                    ],
                    'stores' => $stores,
                ];
            }
            $productResults['slug'] = $slug;
            return $productResults;
        }
    }


    public static function getPimCategoryProductIds($merchantCategoryIds) {
        return self::whereHas('category', function ($records) use ($merchantCategoryIds)
        {
            $records->whereIn('category_id', $merchantCategoryIds)->orderBy('id','ASC');
        })->pluck('id');
    }

    /*
     *
     *  MERCHANT STORE
     *
    */

    public static function getMerchantPimAvailableCategories($merchantId, $store)
    {
        $catalogProductType = optional($store->merchant->merchantProductSettings)->catalog_products_type;
        $pimProductIds = PimProduct::where('merchant_id', $merchantId)
            ->where('product_delivery_type', $catalogProductType)
            ->where('pim_products.is_active',Constant::Yes)
            ->where('store_id', $store->id)
            ->whereHas("activeVariants")
            ->pluck('id')
            ->toArray();
        return PimCategory::select('pim_categories.id', 'name', 'name_ur', 'handle as slug', 'image as banner', 'is_default')
            ->join('pim_product_categories', 'pim_categories.id', 'pim_product_categories.category_id')
            ->whereIn('pim_product_categories.product_id', $pimProductIds)
            ->where('store_id', $store->id)
            ->where('is_active', Constant::Yes)
            ->groupBy('pim_product_categories.category_id')
            ->orderBy('position', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
    }


    public static function getByImportedId($importedId)
    {
        return self::where('imported_product_id', $importedId)->first();
    }

    public static function getProductsForZoodpay( $storeId )
    {
        $fields = [
          'pim_products.id as id',
          'name',
          'sku',
          'price',
          'is_active as status',
          'max_quantity',
          'short_description as description',
          'has_variants',
          'merchant_id',
          'imported_product_id'
        ];

        $category = null;

        $products = self::select($fields)
          ->where('store_id', $storeId)
          ->where('pim_products.is_imported', Constant::Yes)
          ->with([
            'images',
            'category' => function($query) {
                $query->select([
                  'id',
                  'product_id',
                  'category_id',
                ])
                  ->whereHas('category')->with('category:id,name');
            },
            'attribute' => function($query) {
                $query->select([
                  'id',
                  'product_id',
                  'attribute_id',
                  'attribute_value',
                ])
                  ->whereHas('options');
            },
            "variants" => function($query) {
                $query->select([
                  'id as variant_id',
                  'imported_variant_id',
                  'product_variant as variant_name',
                  'product_id',
                  'sku',
                  'quantity',
                  'price',
                  'is_active',
                  'image_id',
                  'track_quantity',
                  'short_description as variant_short_description',
                ])->where('is_active', Constant::Yes);

            }
          ])
          ->whereHas('variants')
          ->paginate(10);


        $productsTransformed = $products->getCollection();

        $productsTransformed = $productsTransformed
          ->map(function($item) {
              $defaultImage = Helper::getProductImagePlaceholder();
              $product = [
                'productId' => $item->id,
                'importedProductId' => $item->imported_product_id,
                'sku' => $item->sku,
                'name' => $item->name,
                'defaultPrice' => $item->price,
                'crossedPrice' => $item->price,
                'quantity'  => 0,
                'description' => $item->description,
                'status' => $item->is_active ? 'Enabled' : 'Disabled',
                'currency' => optional($item->merchant)->getAnalyticCurrencyCode(),
                'skuType' => Constant::ZOODPAY_SKU_TYPES['SINGLE_VARIANT'],
                'propertyName'  => null,
                'property'      => [],
                'categories'    => [],
              ];
              if (!empty($item->images->toArray())){
                  $product['pictures'] = array_column($item->images->toArray(), 'url');
              }else{
                  $product['pictures'][] = $defaultImage;
              }
              if ($item->has_variants){
                  unset($product['quantity']);
                  $attributes = array_column($item->attribute->toArray(), 'attribute_value');
                  $product['propertyName'] = implode('|', $attributes);
                  if (!empty($item->variants) && count($item->variants->toArray()) > 1){
                      $product['skuType'] = Constant::ZOODPAY_SKU_TYPES['MULTI_VARIANT'];
                  }
                  foreach ($item->variants as $variant){
                      $product['property'][] = [
                        "id" => $variant->variant_id,
                        'importedVariantId' => $variant->imported_variant_id,
                        "sku" => $variant->sku,
                        "propertyValue" => $variant->variant_name,
                        "status" => $variant->is_active ? 'Enabled' : 'Disabled',
                        "propertyImage" => optional($variant->image)->url ?? $defaultImage,
                        "price" => $variant->price,
                        "quantity" => $variant->quantity,
                        "track_quantity" => $variant->track_quantity,
                        "onSale" => "0"
                      ];
                  }
              }else{
                  $product['quantity'] = $item->variants[0]->quantity;
              }
              if (!empty($item->category)){
                  $product['categories'] = array_column($item->category->toArray(), 'category');
              }
              return $product;
          })
          ->toArray();

        $finalResult = new \Illuminate\Pagination\LengthAwarePaginator(
          $productsTransformed,
          $products->total(),
          $products->perPage(),
          $products->currentPage(), [
            'path' => \Request::url(),
            'query' => [
              'page' => $products->currentPage(),
            ]
          ]
        );
        $productResults = [
          'products' => $finalResult,
        ];

        return $productResults;
    }

    public static function deleteProducts($storeId, $products)
    {
        return self::where('store_id', $storeId)
          ->where('is_imported', Constant::Yes)
          ->whereNotIn('imported_product_id', $products)
          ->delete();
    }

    public static function isUsedSKU($storeId, $product)
    {
        return self::where('store_id', $storeId)
          ->where('sku', $product['sku'])
          ->whereNull('imported_product_id')
          ->count();

    }

    public static function deleteProductByImportedId($storeId, $importedProductId)
    {
        $product =  self::where('store_id', $storeId)
          ->where('imported_product_id', $importedProductId)
          ->first();
        if ($product){
            $product->delete();
        }
        return $product;
    }

    public static function saveProduct($productData)
    {
        $product = [
          'merchant_id'               => $productData['merchant_id'],
          'store_id'                  => $productData['store_id'],
          'name'                      => $productData['name'],
          'name_ur'                   => $productData['name_ur'],
          'short_description'         => $productData['short_description'],
          'handle'                    => $productData['handle'],
          'tags'                      => $productData['tags'],
          'uuid'                      => $productData['uuid'],
          'sku'                       => $productData['sku'],
          'price'                     => $productData['price'],
          'currency_id'               => $productData['currency_id'],
          'weight'                    => $productData['weight'] ?? 0,
          'brand_id'                  => $productData['brand_id'],
          'is_imported'               => Constant::Yes,
          'max_quantity'              => $productData['max_quantity'],
          'journey_id'                => $productData['journey_id'],
          'is_active'                 => ($productData['stock_status'] != 'outofstock') ? Constant::Yes : Constant::No
        ];

        return self::updateOrCreate(
          [
            'imported_product_id' => $productData['imported_product_id'],
            'store_id' => $productData['store_id']
          ],
          $product
        );
    }

    public function enableVariants()
    {
        $this->has_variants = Constant::Yes;
        $this->save();
    }

    public function disableVariants()
    {
        $this->has_variants = Constant::No;
        $this->save();
    }

    public function disableProduct()
    {
        $this->is_active = Constant::No;
        $this->save();
    }

}