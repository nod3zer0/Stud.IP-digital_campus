<?php

namespace JsonApi\Routes\StockImages;

use StockImage;
use User;

class Authority
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canIndexStockImages(User $user): bool
    {
        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canShowStockImage(User $user, StockImage $resource): bool
    {
        return true;
    }

    /**
     * @SuppressWarnings(PHPMD.Superglobals)
     */
    public static function canCreateStockImage(User $user): bool
    {
        return $GLOBALS['perm']->have_perm('admin', $user->id);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canUpdateStockImage(User $user, StockImage $resource): bool
    {
        return self::canCreateStockImage($user);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canUploadStockImage(User $user, StockImage $resource): bool
    {
        return self::canCreateStockImage($user);
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function canDeleteStockImage(User $user, StockImage $resource): bool
    {
        return self::canCreateStockImage($user);
    }
}
