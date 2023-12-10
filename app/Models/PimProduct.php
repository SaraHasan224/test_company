<?php

namespace App\Models;

use App\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use App\Helpers\Constant;
use Illuminate\Database\Eloquent\SoftDeletes;
use function Illuminate\Process\options;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PimProduct extends Model
{
    protected $guarded = [];
    public static $validationRules = [
        'addToCart' => [
            'product_id' => 'required|numeric|exists:pim_products,id',
            'customer_address_id' => 'required|numeric|exists:customer_addresses,id',
        ],
        'store' => [
            'store_slug' => 'required',
        ],
        'office_categories' => [
            'store_slug' => 'required|string|exists:merchant_stores,store_slug',
            'category_slug' => 'required|exists:pim_categories,handle'
        ],
    ];
    public static function getValidationRules( $type, $params = [] )
    {
        $cartAllowedActions = array_key_exists('cart_actions_allowed', $params) ? $params['cart_actions_allowed'] : [];
        $allowedActions = array_key_exists('actions_allowed', $params) ? $params['actions_allowed'] : [];
        $productId = array_key_exists('product_id', $params) ? $params['product_id'] : [];
        $customerId = array_key_exists('customer_id', $params) ? $params['customer_id'] : [];
        $cartId = array_key_exists('cart_id', $params) ? $params['cart_id'] : [];
        $officeId = array_key_exists('office_id', $params) ? $params['office_id'] : [];
        $rules = [
            'add-product' => [
                "name" => "required|string",
                'sku'      => 'required|string|'.Rule::unique('pim_products', 'sku')->where('office_id', $officeId),
                "short_description" => "required|string",
                "price" => 'required|numeric',
                "discounted_price" => 'required|numeric|lt:price',
                'max_quantity' => 'required|numeric|gt:0',

                'category' => 'required',
                'category.parent' => 'required|string|'.Rule::exists('pim_bs_categories', 'slug'),
                'category.child' => 'nullable|string|'.Rule::exists('pim_bs_categories', 'slug'),

                'brands' => 'required',
                'brands.value' => 'required|numeric|'.Rule::exists('pim_brands', 'id'),

                'variants' => 'required',
                'variants.*.price' => 'required|numeric',
                'variants.*.discounted_price' => 'required|numeric',
                'variants.*.qty' => 'required|numeric|gt:0',
                'variants.*.description' => 'required|string',

                'variants.variation.*' => 'required|array',
                'variants.variation.*.name' => 'required|string|'.Rule::exists('pim_attributes', 'name'),
                'variants.variation.*.value' => 'required|string|'.Rule::exists('pim_attribute_options', 'option_value'),

                'images' => 'required',

                'shipment' => 'required',
                'shipment.country' => 'required|numeric|'.Rule::exists('countries', 'id'),
                'shipment.freeShipping' => 'nullable|boolean',
                'shipment.shippingPrice' => 'nullable|numeric|gt:0',
                'shipment.worldWideShipping' => 'nullable|boolean',
            ],
            'filters' => [
                'filters' => 'required',
                'filters.price_range' =>  "required",
                'filters.price_range.min' =>  "nullable",
                'filters.price_range.max' =>  "nullable",
                'filters.office_reference' =>  "nullable",
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
            ],
            'remove-cart-item' => [
                'cart_item_id' => 'nullable|numeric|'.Rule::exists('customer_cart_items', 'id')->where('cart_id', $cartId),
                'product_id' => 'required|numeric|'.Rule::exists('pim_products', 'id'),
                'product_variant_id' => 'required|numeric|'.Rule::exists('pim_product_variant', 'id')->where('product_id', $productId),
                'product_qty' => 'nullable|numeric|gt:0',
            ],
            'delete-cart-item' => [
                'cart_item_id' => 'nullable|numeric|'.Rule::exists('customer_cart_items', 'id')->where('cart_id', $cartId),
                'product_id' => 'required|numeric|'.Rule::exists('pim_products', 'id'),
                'product_variant_id' => 'required|numeric|'.Rule::exists('pim_product_variant', 'id')->where('product_id', $productId),
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

    public function attributeOption()
    {
        return $this->hasMany(PimProductVariantOption::class, 'product_id', 'id');
    }

    public function activeVariants()
    {
        return $this->hasMany(PimProductVariant::class, 'product_id', 'id')
            ->where('status', Constant::Yes)
            ->with([
                'image:id,url,position',
            ]);
    }

    public function category()
    {
        return $this->hasMany(PimProductCategory::class, 'product_id', 'id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }

    public function brand()
    {
        return $this->hasOne(PimBrand::class, 'id', 'brand_id');
    }

    public function shipmentCountryDetails()
    {
        return $this->hasOne(Country::class, 'id', 'shipment_country');
    }

    public function images()
    {
        return $this->hasMany(PimProductImage::class, 'product_id', 'id')
            ->where('status',Constant::Yes)->orderBy('position')->select('id','product_id','url');
    }

    public function defaultImage()
    {
        $defaultImage = PimProductImage::getPlaceholder();
        return $this->hasOne(PimProductImage::class, 'product_id', 'id')
            ->where('is_default',Constant::Yes)
            ->withDefault([
                'id' => optional($defaultImage)->id,
                'product_id' => optional($defaultImage)->product_id,
                'url' => !empty($defaultImage) ? optional($defaultImage)->getAttributes()['url'] : PimProductImage::getPlaceholder(),
            ]);
    }

    public static function findById($id)
    {
        return self::where('id', $id)->first();
    }

    public static function getById($id)
    {
        return self::where('id', $id)->where('status', Constant::Yes)->first();
    }

    public static function getByHandle($handle)
    {
        return self::where('handle', $handle)->where('status', Constant::Yes)->first();
    }

    public static function findProductIdsByOffice($officeId)
    {
        return self::where('office_id', $officeId)->where('status', Constant::Yes)->get()->pluck('id');
    }

    public static function getProductDetail($productHandle)
    {
        $fields = [
            'pim_products.id as id',
            'name',
            'price',
            'handle',
            'office_id',
            'max_quantity',
            'short_description',
            'shipment_country',
            'free_shipment',
            'enable_world_wide_shipping',
            'shipping_price',
            'pim_products.has_variants as has_variants',
        ];

        $product = self::where("handle",$productHandle)
            ->select($fields)
            ->with([
                'shipmentCountryDetails:id,name',
                'category',
                'defaultImage:id,product_id,url,position',
                'office' => function($query) {
                    $query->select([
                        'id',
                        'office_name',
                        'office_reference',
                        'logo',
                        'about_office'
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
                    $query->where('status', Constant::Yes);
                }
            ])
            ->whereHas('activeVariants', function($query) {
                $query->where('status', Constant::Yes);
            })
            ->where('pim_products.status', Constant::Yes)->first();
        if(!empty($product)) {
            $office = $product->office;
            $defaultImage = PimProductImage::getPlaceholder();
            $product['images'] =  $product->images()->count() == 0 ? [
                [
                    'id' => $defaultImage->id,
                    'product_id' => $defaultImage->product_id,
                    'url' => $defaultImage->url,
                ]
            ] : $product->images;

            $image = optional(optional($product)->defaultImage)->url;
            $product['discounted_price'] = 0;
            $product['image'] = !empty($image) ? $image : Helper::getProductImagePlaceholder();
            $product['office'] = [
                'name' => $office->office_name,
                'reference' => $office->office_reference,
                'logo' => $office->logo,
                'website' => $office->about_office,
            ];
            $productVariants = $product->activeVariants;
            $variantAttributes = [];
            $attributeOptions = [];
            $availableOptions = [];
            $color = [];
            $condition = [];
            $size = [];
            $standard = [];
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
                        $optionVal = [];
                        $optionsAllowed = array_key_exists($productAttribute->attribute_id, $availableOptions) ? $availableOptions[$productAttribute->attribute_id] : [];

                        foreach ($productAttribute['options'] as $option) {
                            if(array_key_exists(0,$availableOptions[$productAttribute->attribute_id])){
                                $options[] = [
                                    'id' => $option->option_id,
                                    'value' => $option->option_value,
                                ];
//                                $optionVal[] = [
//                                    'label' => $option->option_label,
//                                    'value' => $option->option_value,
//                                    'attribute_id' => $option->attribute_id
//                                ];
                                $optionVal[] = $option->option_value;
                            }else {
                                if(in_array($option->option_id, $optionsAllowed)){
                                    $options[] = [
                                        'id' => $option->option_id,
                                        'value' => $option->option_value,
                                    ];
//                                $optionVal[] = [
//                                    'label' => $option->option_label,
//                                    'value' => $option->option_value,
//                                    'attribute_id' => $option->attribute_id
//                                ];
                                    $optionVal[] = $option->option_value;
                                }
                            }
                        }
                        $_attrDetail = [
                            'id' => $productAttribute->attribute_id,
                            'name' => $productAttribute->attribute_value,
                            'options' => $options
                        ];
                        $optionVal = [];
                        if($productAttribute->attribute_value == "color") {
                            $color = $optionVal;
                        }else if($productAttribute->attribute_value == "condition") {
                            $condition = $optionVal;
                        }
                        if($productAttribute->attribute_value == "size") {
                            $size = $optionVal;
                        }
                        if($productAttribute->attribute_value == "standard") {
                            $standard = $optionVal;
                        }

                        $attributes[] = $_attrDetail;
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
                    'max_quantity' => $product->max_quantity,
                    'price' => $price,
                    'sku' => $productVariant->sku,
                    'variant_id' => $productVariant->id,
                    'variant_name' => $productVariant->name,
                    'variant_short_description' => $productVariant->short_description,
                    'attributes' => $productVariantAttributes,
                ];
            }
            $categories = [];
            $sub_categories = [];
            foreach ($product->category as $category) {
                if($category->category->parent_id == 0) {
                    $categories[] = optional($category->category)->name;
                }else {
                    $sub_categories[] = optional($category->category)->name;
                    $categories[] = optional(optional($category->category)->parentCategory)->name;
                }
            }

            $product['variants'] = $productVariantDetails;
            $product['item_information'] = [
                "category" => $categories,
                "subCategory" => $sub_categories,
				"brand" => $product->brand,
                "condition" => $condition,
                "size" => $size,
                "standard" => $standard,
                "color" => $color,
            ];
            $product['variant_ref'] = $attributeOptions;
            $product['default_variant_id'] = $defaultVariantId;
            $product['attributes'] = $attributes;

            unset($product['category']);
            unset($product['office_id']);
            unset($product['defaultImage']);
            unset($product['attribute']);
            unset($product['attributeOption']);
            unset($product['office']);
            unset($product['activeVariants']);
            unset($product['id']);
        }
        return $product;
    }

    public static function getProductsForApp($listingType = Constant::PJ_PRODUCT_LIST['FEATURED_PRODUCTS'], $perPage, $listOptions = [], $disablePagination = false)
    {
        $slug = "";
        $page = array_key_exists('page', $listOptions) ? $listOptions['page'] : 1;
        $office = array_key_exists('office', $listOptions) ? $listOptions['office'] : [];
        $categoryId = array_key_exists('categoryId', $listOptions) ? $listOptions['categoryId'] : [];
        $categoryIds = array_key_exists('categoryIds', $listOptions) ? $listOptions['categoryIds'] : [];
        $customerId = array_key_exists('customer_id', $listOptions) ? $listOptions['customer_id'] : [];
        $bsCategorySlug = array_key_exists('bsCategorySlug', $listOptions) ? $listOptions['bsCategorySlug'] : '';
        $filters = array_key_exists('filters', $listOptions) ? $listOptions['filters'] : '';
        $limitRecord = array_key_exists('limit_record', $listOptions) ? $listOptions['limit_record'] : '';
        $filterByProductIds = array_key_exists('filter_by_product_ids', $listOptions) ? $listOptions['filter_by_product_ids'] : "";

        $fields = [
            'pim_products.id as id',
            'name',
            'price',
            'office_id',
            'brand_id',
            'handle',
            'max_quantity',
            'short_description',
            'has_variants',
            'featured_position',
            'pim_products.position as position',
            'rank',
            'shipping_price',
            'enable_world_wide_shipping',
            'shipment_country'
        ];

        if ($listingType == Constant::PJ_PRODUCT_LIST['RECENTLY_VIEWED_PRODUCTS']) {
            $fields[] = 'cp_recently_viewed.created_at as recently_viewed_created_at';
        }
        $skipRecord = false;
        $skipCount = true;
        $category = null;
        $productListing = array_flip(Constant::PJ_PRODUCT_LIST);
        $type = $productListing[$listingType];

        if ($listingType == Constant::PJ_PRODUCT_LIST['CATEGORY_PRODUCTS'] && !empty($bsCategorySlug)) {
            $slug = $bsCategorySlug;
            $category = PimBsCategory::getCategoryBySlug($bsCategorySlug);
        }
        $products = self::select($fields);

        if($listingType == Constant::PJ_PRODUCT_LIST['RECENTLY_VIEWED_PRODUCTS']){
            $products = $products->join('customer_product_recently_viewed as cp_recently_viewed', 'cp_recently_viewed.product_id', '=', 'pim_products.id')
                        ->where('cp_recently_viewed.customer_id', $customerId)
                        ->orderBy('cp_recently_viewed.viewed_at', 'DESC');
            if(!empty($excludedProductId)){
                $products->where('pim_products.id', '<>', $excludedProductId);
            }
        }

        if (!empty($filterByProductIds)) {
            $products = $products->whereIn('pim_products.id', $filterByProductIds);
        }

        $products = $products->with([
            'defaultImage:id,product_id,url,position',
            'office' => function ($query) {
                $query->select([
                    'id',
                    'office_name',
                    'office_reference',
                    'logo',
                    'banner',
                ])->where('status', Constant::Yes);
            },
            'attribute' => function ($query) {
                $query->select([
                    'id',
                    'product_id',
                    'attribute_id',
                    'attribute_value',
                ])
                    ->with(['options:id,product_id,attribute_id,pim_product_attribute_id,option_id,option_value'])
                    ->whereHas('options');
            },
            'attributeOption' => function ($query) {
                $query->select([
                    'id',
                    'product_id',
                    'variant_id',
                    'attribute_id',
                    'option_id'
                ])->whereHas('variant');
            },
            "activeVariants" => function ($query) {
                $query->select([
                    'id as variant_id',
                    'product_variant as variant_name',
                    'product_id',
                    'sku',
                    'quantity as max_quantity',
                    'price',
                    'discount',
                    'discount_type',
                    'status',
                    'image_id',
                    'short_description as variant_short_description',
                ])->where('status', Constant::Yes);

            },
        ])
            ->whereHas('activeVariants', function ($query) {
                $query->where('status', Constant::Yes);
            })
            ->where('pim_products.status', Constant::Yes);

        $products->orderBy('id','DESC');

        $filteredProducts = $products;
        $filtersSortBy = "";
        $filtersApplied = false;
        if(!empty($filters)){
            if(array_key_exists('price_range', $filters)){
                if(array_key_exists('min', $filters['price_range']) &&  $filters['price_range']['min'] >= 0 && !empty($filters['price_range']['max'])){
                    $filter_min_price = $filters['price_range']['min'];
                    $filter_max_price = $filters['price_range']['max'];
                    if($filter_max_price > 0 && $filter_min_price < $filter_max_price) {
                        $filteredProducts->whereBetween('pim_products.price', [$filter_min_price, $filter_max_price]);
                    }
                    $filtersApplied = true;
                }
            }

            if(array_key_exists('price_high_to_low', $filters['sort_by']) &&  $filters['sort_by']['price_high_to_low'] == 1){
                $filtersSortBy = "price_high_to_low";
                $filteredProducts->orderBy('pim_products.price', "DESC");
                $filtersApplied = true;
            }
            else if(array_key_exists('price_low_to_high', $filters['sort_by']) &&  $filters['sort_by']['price_low_to_high'] == 1){
                $filtersSortBy = "price_low_to_high";
                $filteredProducts->orderBy('pim_products.price', "ASC");
                $filtersApplied = true;
            }
            else if(array_key_exists('newest_arrival', $filters['sort_by']) &&  $filters['sort_by']['newest_arrival'] == 1){
                $filtersSortBy = "newest_arrival";
                $filteredProducts->orderBy('pim_products.id', "DESC");
                $filtersApplied = true;
            }
            else if(array_key_exists('featured', $filters['sort_by']) &&  $filters['sort_by']['featured'] == 1){
                $filtersSortBy = "featured";
                $filteredProducts->where('is_featured', Constant::Yes);
                $filteredProducts->orderBy('featured_position','ASC');
                $filtersApplied = true;
            }

            if($filters['records_range']['show_count'] > 0) {
                $limitRecord = $filters['records_range']['show_count'];
            }

            if(!empty($filters['categories'])) {
                $categories = explode($filters['categories'], ",");
            }
            if(!empty($filters['brands'])) {
                $brands = explode($filters['brands'], ",");
            }
            if(!empty($filters['condition'])) {
                $condition = explode($filters['condition'], ",");
            }
            if(!empty($filters['size'])) {
                $size = explode($filters['size'], ",");
            }
            if(!empty($filters['standard'])) {
                $standard = explode($filters['standard'], ",");
            }
            if(!empty($filters['color'])) {
                $colors = explode($filters['color'], ",");
            }
        }


        if($listingType == Constant::PJ_PRODUCT_LIST['FEATURED_PRODUCTS']){
            $products->where('is_featured', Constant::Yes);
            $products->orderBy('featured_position','ASC');

            if($filtersApplied) {
                $filteredProducts->where('is_featured', Constant::Yes);
                $filteredProducts->orderBy('featured_position','ASC');
            }
        }
        else if($listingType == Constant::PJ_PRODUCT_LIST['CLOSET_PRODUCTS'] && !empty($office)){
            $products->where('pim_products.office_id', $office->id);
            if(!empty($categoryId)){
                $products->whereHas('category', function ($query) use ($categoryId) {
                    $query->where('category_id',$categoryId);
                });
            }
            $products->orderBy('id','DESC');
//            $products->orderBy('rank','ASC');

            if($filtersApplied) {
                $filteredProducts->where('pim_products.office_id', $office->id)
                                ->where('pim_products.is_recommended', Constant::Yes);
                if(!empty($categoryId)){
                    $filteredProducts->whereHas('category', function ($query) use ($categoryId) {
                        $query->where('category_id',$categoryId);
                    });
                }
                $filteredProducts->orderBy('recommended_position','DESC');
            }
        }else if($listingType == Constant::PJ_PRODUCT_LIST['CLOSET_TRENDING_PRODUCTS'] && !empty($office)){
            $products->where('pim_products.office_id', $office->id);
            if(!empty($categoryId)){
                $products->whereHas('category', function ($query) use ($categoryId) {
                    $query->where('category_id',$categoryId);
                });
            }
            $products->orderBy('rank','ASC');
            $skipCount = 0;
            $skipRecord = true;
        }
        else if($listingType == Constant::PJ_PRODUCT_LIST['CATEGORY_PRODUCTS'] && !empty($categoryIds)){
            $products->with([
                'category' => function($query) use($categoryId) {
                    $query->select([
                        'product_id',
                        'category_id',
                    ]);
                },
            ])->whereHas('category', function ($records) use ($categoryIds)
            {
                $records->whereIn('category_id', $categoryIds)->orderBy('id','ASC');
            });
            $products->orderBy('rank','ASC');

            if($filtersApplied) {
                $filteredProducts->with([
                    'category' => function($query) use($categoryId) {
                        $query->select([
                            'product_id',
                            'category_id',
                        ]);
                    },
                ])->whereHas('category', function ($records) use ($categoryIds)
                {
                    $records->whereIn('category_id', $categoryIds)->orderBy('id','ASC');
                });
                $filteredProducts->orderBy('rank','ASC');
            }
        }
        else if($listingType == Constant::PJ_PRODUCT_LIST['RECENTLY_VIEWED_PRODUCTS']){
            $products->orderBy('recently_viewed_created_at','DESC');
        }


        $productMax = $products->max('price');
        $productMin = $products->min('price');

        // IF $skipRecord is true then use ;
        if ($skipRecord || $page != 1) {
            if(!$skipCount) {
                $skipCount = $page*$perPage - $perPage;
                $limitRecord = $perPage;
            }
            if (!$filtersApplied) {
                $productList = $products->offset($skipCount)->limit($limitRecord)->get();
            } else {
                $productList = $filteredProducts->offset($skipCount)->limit($limitRecord)->get();
            }
            $productsTransformed = $productList;
        } else {
            if (!$filtersApplied) {
                $productList = $products->paginate($perPage);
            } else {
                $productList = $filteredProducts->paginate($perPage);
            }
            $productsTransformed = $productList->getCollection();
        }

        $productsTransformed = $productsTransformed
            ->map(function ($item) use ($slug, $listingType, $category, $filters) {
                $defaultVariant = $item->activeVariants->first();
                $discountType = $defaultVariant->discount_type;
                $price = $defaultVariant->price;
                $discount = $defaultVariant->discount;
                $discountedPrice = $defaultVariant->discounted_price;
                if ($listingType == Constant::PJ_PRODUCT_LIST['CATEGORY_PRODUCTS']) {
                    $position = $item->rank;
                } else if ($listingType == Constant::PJ_PRODUCT_LIST['FEATURED_PRODUCTS']) {
                    $position = $item->featured_position;
                }  else if ($listingType == Constant::PJ_PRODUCT_LIST['CLOSET_TRENDING_PRODUCTS']) {
                    $position = $item->recommended_position;
                } else {
                    $position = $item->position;
                }

                $image = optional(optional($item)->defaultImage)->url;
                $pimCategory = [];
                foreach ($item->category as $_category) {
                    $pimCategory[] = $_category->category->name." / ". optional(optional($_category->category)->parentCategory)->name;
                }
                return [
                    'id' => $item->id,
                    'name' => $item->name,
                    'handle' => $item->handle,
                    'list_type' => $listingType,
                    'discount' => empty($discount) ? 0 : $discount,
                    'price' => $price,
                    'discounted_price' => $discountedPrice,
                    'discount_badge' => [
                        'show' => ($discountedPrice >= $price) ? Constant::No : Constant::Yes,
                        'discount' => $discount,
                        'type' => $discountType,
                    ],
                    'quantity' => $item->max_quantity,
                    'has_variants' => $item->has_variants,
                    'image' => !empty($image) ? $image : Helper::getProductImagePlaceholder(),
                    'images' => $item->images,
                    'default' => $item->images->first(),
                    'position' => $position,
                    'shipping_cost' => (int) $item->shipping_price,
                    'description' => $item->short_description,
                    'variant_count' => $item->activeVariants->count(),
                    'attribute_count' => $item->attribute->count(),
                    'default_variant_id' => $defaultVariant->variant_id,
                    'category' => $pimCategory,
                    'category_name' => !empty($category) && !empty($category->parent) ? $category->parent->name : optional($category)->name,
                    'category_id' => !empty($category) && !empty($category->parent) ? $category->parent->id : optional($category)->id,
                    'sub_category_name' => !empty($category) && !empty($category->parent) ? $category->name : null,
                    'sub_category_id' => !empty($category) && !empty($category->parent) ? $category->id : null,
                    'office_name' => $item->office->office_name,
                    'office_slug' => $item->office->office_reference,
                    'office_favicon' => $item->office->logo,
                ];
            })
            ->toArray();

        if ($disablePagination) {
            return $productsTransformed;
        } else {
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
            $conditionAttributeId = PimAttribute::getAttributeByName('Condition')->id;
            $sizeAttributeId = PimAttribute::getAttributeByName('Size')->id;
            $standardAttributeId = PimAttribute::getAttributeByName('Standard')->id;
            $colorAttributeId = PimAttribute::getAttributeByName('Color')->id;

            $sortByFilters = Constant::SORT_BY_FILTERS;
            if ($listingType == Constant::PJ_PRODUCT_LIST['FEATURED_PRODUCTS']) {
                unset($sortByFilters['featured']);
            }
            $productResults['slug'] = $slug;
            $productResults['filters'] = [
                'categories' => PimBsCategory::getAllProductCategories(),
                'brands' => PimBrand::getAllBrandCategories(),
                'condition' => self::formatMetaOptions(PimAttributeOption::getByAttributeId($conditionAttributeId)),
                'size' => self::formatMetaOptions(PimAttributeOption::getByAttributeId($sizeAttributeId)),
                'standard' => self::formatMetaOptions(PimAttributeOption::getByAttributeId($standardAttributeId)),
                'color' => self::formatMetaOptions(PimAttributeOption::getByAttributeId($colorAttributeId)),
                'sort_by' => $sortByFilters,
                'price_range' => [
                    'max' => $productMax,
                    'min' => $productMin,
                ],
            ];
            $productResults['slug'] = $slug;
            $productResults['per_page_count'] = $perPage;
            $productResults['sort_by'] = $filtersSortBy;
            return $productResults;
        }
    }

    private static function formatMetaOptions($array)
    {
        $arrayObj = [];
        foreach ($array as $key => $value) {
            $arrayObj[] = [
                'label' => $value->option_label,
                'value' => $value->option_value,
                'attribute_id' => $value->attribute_id,
                'option_id' => $value->id
            ];
        }
        return $arrayObj;
    }

    public static function getPimCategoryProductIds($categoryId) {
        return self::whereHas('category', function ($records) use ($categoryId)
        {
            $records->whereIn('category_id', $categoryId)->orderBy('id','ASC');
        })->pluck('id');
    }

    public static function getMerchantPimAvailableCategories($office)
    {
        $pimProductIds = PimProduct::where('pim_products.status',Constant::Yes)
            ->where('office_id', $office->id)
            ->whereHas("activeVariants")
            ->pluck('id')
            ->toArray();
        return PimCategory::select('pim_categories.id', 'name', 'name_ur', 'handle as slug', 'image as banner', 'is_default')
            ->join('pim_product_categories', 'pim_categories.id', 'pim_product_categories.category_id')
            ->whereIn('pim_product_categories.product_id', $pimProductIds)
            ->where('office_id', $office->id)
            ->where('status', Constant::Yes)
            ->groupBy('pim_product_categories.category_id')
            ->orderBy('position', 'ASC')
            ->orderBy('id', 'DESC')
            ->get();
    }


    public static function deleteProducts($officeId, $product)
    {
        return self::where('office_id', $officeId)->whereId($product->id)
          ->delete();
    }

    public static function isUsedSKU($officeId, $product)
    {
        return self::where('office_id', $officeId)
          ->where('sku', $product['sku'])
          ->count();

    }

    public static function saveProduct($productData)
    {
        $product = [
          'merchant_id'               => $productData['merchant_id'],
          'office_id'                  => $productData['office_id'],
          'name'                      => $productData['name'],
          'name_ur'                   => $productData['name_ur'],
          'short_description'         => $productData['short_description'],
          'handle'                    => $productData['handle'],
          'tags'                      => $productData['tags'],
          'uuid'                      => $productData['uuid'],
          'sku'                       => $productData['sku'],
          'price'                     => $productData['price'],
          'weight'                    => $productData['weight'] ?? 0,
          'brand_id'                  => $productData['brand_id'],
          'is_imported'               => Constant::Yes,
          'max_quantity'              => $productData['max_quantity'],
          'journey_id'                => $productData['journey_id'],
          'status'                 => ($productData['stock_status'] != 'outofstock') ? Constant::Yes : Constant::No
        ];

        return self::updateOrCreate(
          [
            'imported_product_id' => $productData['imported_product_id'],
            'office_id' => $productData['office_id']
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
        $this->status = Constant::No;
        $this->save();
    }

    public static function getByFilters($filter, $ref)
    {
        $fields = [
            'pim_products.id as id',
            'name',
            'price',
            'brand_id',
            'handle',
            'max_quantity',
            'short_description',
            'has_variants',
            'featured_position',
            'pim_products.position as position',
            'rank',
            'status',
            'shipping_price',
            'enable_world_wide_shipping',
            'shipment_country',
            'created_at',
            'is_featured',
            'featured_position',
            'featured_at',
            'featured_by',
        ];

        $products = self::select($fields)
            ->where('office_id', $ref)
            ->with([
                'defaultImage:id,product_id,url,position',
                'category.category.parentBSCategory',
                'shipmentCountryDetails:id,name',
                'attribute' => function ($query) {
                    $query->select([
                        'id',
                        'product_id',
                        'attribute_id',
                        'attribute_value',
                    ])
                        ->with(['options:id,product_id,attribute_id,pim_product_attribute_id,option_id,option_value'])
                        ->whereHas('options');
                },
                'attributeOption' => function ($query) {
                    $query->select([
                        'id',
                        'product_id',
                        'variant_id',
                        'attribute_id',
                        'option_id'
                    ])->whereHas('variant');
                },
                "activeVariants" => function ($query) {
                    $query->select([
                        'id as variant_id',
                        'product_variant as variant_name',
                        'product_id',
                        'sku',
                        'quantity as max_quantity',
                        'price',
                        'discount',
                        'discount_type',
                        'status',
                        'image_id',
                        'short_description as variant_short_description',
                    ])->where('status', Constant::Yes);

                },
            ])
            ->whereHas('activeVariants', function ($query) {
                $query->where('status', Constant::Yes);
            })->orderBy('id','DESC')->get();
        /*
        if (count($filter))
        {
            if (!empty($filter['name']))
            {
                $data = $data->where('first_name', 'LIKE', '%' . trim($filter['first_name']) . '%')
                    ->orWhere('last_name', 'LIKE', '%' . trim($filter['last_name']) . '%');
            }
            if (!empty($filter['user_name']))
            {
                $data = $data->where('username', 'LIKE', '%' . trim($filter['user_name']) . '%');
            }

            if (!empty($filter['phone']))
            {
                $phone = trim($filter['phone']);
                $phone = Helper::formatPhoneNumber($phone);
                $data = $data->where('phone', 'LIKE', '%' . $phone . '%');
            }

            if (!empty($filter['email']))
            {
                $data = $data->where('email', 'LIKE', '%' . trim($filter['email']) . '%');
            }

            if (!empty($filter['last_login']))
            {
                $memberSince = trim($filter['last_login']);
                $data = $data->whereDate('last_login', '>=', date('Y-m-d', strtotime($memberSince)));
            }

            if (isset($filter['status']))
            {
                $data = $data->where('status', $filter['status']);
            }

            if (isset($filter['subscription_status']))
            {
                $data = $data->where('subscription_status', $filter['subscription_status']);
            }
        }
        */
        $count = $products->count();

        return [
            'count'   => $count,
            'offset'  => isset($filter['start']) ? $filter['start'] : 0,
            'records' => $products
        ];
    }

    public static function getOfficeListing($perPage = "", $type, $disablePagination = false)
    {
        $fields = [
            'id',
            'customer_id',
            'office_name',
            'logo',
            'office_reference',
        ];
        $query = self::select($fields)->where('status', Constant::Yes);
//
        $query->whereHas('customer', function($query) {
            $query->where('status',Constant::Yes);
        });
        if($type == Constant::PJ_CLOSETS_LIST_TYPES['Trending']) {
//            $query->where('is_trending', Constant::Yes)
//                  ->orderBy('trending_position', 'DESC');
        }else {
            $query->orderBy('office_name', 'ASC');

        }

        $officeList = $query
            ->whereHas('products')
            ->paginate($perPage);

        $officeTransformed = $officeList
            ->getCollection()
            ->map(function ($item) use($type){
                if($type == Constant::PJ_CLOSETS_LIST_TYPES['Trending']){
                    $item['country'] = $item->customer->country->name;
                }
                unset($item->customer);
                unset($item->id);
                unset($item->customer_id);
                return $item;
            })->toArray();
        if($disablePagination) {
            return $officeTransformed;
        }
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $officeTransformed,
            $officeList->total(),
            $officeList->perPage(),
            $officeList->currentPage(), [
                'path' => \Request::url(),
                'query' => [
                    'page' => $officeList->currentPage()
                ]
            ]
        );
    }

    public static function updateFeaturedStatus($handle, $status, $position)
    {
        self::where('handle', $handle)->update([
            'is_featured' => $status,
            'featured_position' => $position,
            'featured_at' => Carbon::now(),
            'featured_by' => Auth::user()->id,
        ]);
    }

    public static function updateStatus($handle, $status)
    {
        self::where('handle', $handle)->update(['status' => $status]);
    }
}
