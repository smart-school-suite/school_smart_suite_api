<?php

namespace App\SchoolBranchSetting\Resolvers;

use App\Exceptions\AppException;
use App\SchoolBranchSetting\Contracts\SettingUpdateHandler;
use App\SchoolBranchSetting\Registry\SettingUpdateRegistry;

class SettingUpdateResolver
{
    public function __construct(
        private SettingUpdateRegistry $registry
    ) {}

    public function resolve(string $settingKey): SettingUpdateHandler
    {
        $handlerClass = $this->registry->handlers()[$settingKey] ?? null;

        if (!$handlerClass) {
            throw new AppException(
                "No update handler is registered for {$settingKey}.",
                400,
                "Unsupported Setting",
                "No update handler is registered for {$settingKey}.",
                "setting"
            );
        }

        $handler = app($handlerClass);

        if (!$handler instanceof SettingUpdateHandler) {
            throw new AppException(
                "The handler registered for {$settingKey} is invalid.",
                400,
                "Invalid Setting Handler",
                "The handler registered for {$settingKey} is invalid.",
                "setting"
            );
        }

        return $handler;
    }
}
