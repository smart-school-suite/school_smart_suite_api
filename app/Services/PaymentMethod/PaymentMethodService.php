<?php

namespace App\Services\PaymentMethod;

use App\Exceptions\AppException;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Storage;

class PaymentMethodService
{
    public function createPaymentMethod($data, $request = null)
    {
        $existingMethod = PaymentMethod::where("name", $data["name"])
            ->where('country_id', $data['country_id'])
            ->first();

        if ($existingMethod) {
            throw new AppException(
                "Existing Method",
                409,
                "Existing Method",
                "Payment Method With this name {$data['name']} already exists"
            );
        }

        if ($request && $request->hasFile('operator_img')) {
            $operatorImg = $request->file('operator_img');
            $fileName = time() . '_' . $operatorImg->getClientOriginalName();
            $operatorImg->storeAs('public/PaymentOperators', $fileName);
            $data['operator_img'] = $fileName;
        } else {
            $data['operator_img'] = null;
        }

        return PaymentMethod::create($data);
    }

    public function updatePaymentMethod($data, $paymentMethodId, $request = null)
    {
        $paymentMethod = PaymentMethod::find($paymentMethodId);

        if (!$paymentMethod) {
            throw new AppException(
                "Payment Method Not Found",
                404,
                "Missing Method",
                "The Payment Method you are trying to update was not found. It may have been deleted."
            );
        }

        if (isset($data['name']) || isset($data['country_id'])) {
            $query = PaymentMethod::where('name', $data['name'] ?? $paymentMethod->name)
                ->where('country_id', $data['country_id'] ?? $paymentMethod->country_id)
                ->where('id', '!=', $paymentMethodId);

            if ($query->exists()) {
                throw new AppException(
                    "Existing Method",
                    409,
                    "Existing Method",
                    "A Payment Method with this name already exists in the selected country."
                );
            }
        }

        if ($request && $request->hasFile('operator_img')) {
            if ($paymentMethod->operator_img) {
                Storage::disk('public')->delete('PaymentOperators/' . $paymentMethod->operator_img);
            }

            $operatorImg = $request->file('operator_img');
            $fileName = time() . '_' . $operatorImg->getClientOriginalName();
            $operatorImg->storeAs('public/PaymentOperators', $fileName);
            $data['operator_img'] = $fileName;
        } else {
            unset($data['operator_img']);
        }

        $filteredData = array_filter($data, fn($value) => !is_null($value) && $value !== '');

        if (empty($filteredData)) {
            throw new AppException(
                "No Changes",
                400,
                "No Updates Provided",
                "No valid changes were provided for the update."
            );
        }

        $paymentMethod->update($filteredData);

        return $paymentMethod;
    }
    public function deactivatePaymentMethod($paymentMethodId)
    {
        $paymentMethod = PaymentMethod::find($paymentMethodId);
        if (!$paymentMethod) {
            throw new AppException(
                "Payment Method Not Found",
                404,
                "Payment Method Not Found",
                "Payment Method Not Found Please ensure that this payment method is available and has not been deleted"
            );
        }

        if ($paymentMethod->status == "inactive") {
            throw new AppException(
                "Deactivation Conflict",
                409,
                "Deactivation Conflict",
                "{$paymentMethod->name} already deactivated"
            );
        }

        $paymentMethod->status = "inactive";
        $paymentMethod->save();

        return $paymentMethod;
    }

    public function activatePaymentMethod($paymentMethodId)
    {
        $paymentMethod = PaymentMethod::find($paymentMethodId);
        if (!$paymentMethod) {
            throw new AppException(
                "Payment Method Not Found",
                404,
                "Payment Method Not Found",
                "Payment Method Not Found Please ensure that this payment method is available and has not been deleted"
            );
        }

        if ($paymentMethod->status == "active") {
            throw new AppException(
                "Activation Conflict",
                409,
                "Activation Conflict",
                "{$paymentMethod->name} already Activated"
            );
        }

        $paymentMethod->status = "active";
        $paymentMethod->save();

        return $paymentMethod;
    }

    public function getPaymentMethodCountryId($countryId)
    {
        $paymentMethods = PaymentMethod::where("country_id", $countryId)
            ->with(['category', 'country'])
            ->where("status", "active")
            ->get();
        return $paymentMethods;
    }

    public function deletePaymentMethod($paymentMethodId)
    {
        $paymentMethod = PaymentMethod::find($paymentMethodId);

        if (!$paymentMethod) {
            throw new AppException(
                "Payment Method Not Found",
                404,
                "Missing Method",
                "The Payment Method you are trying to update was not found. It may have been deleted."
            );
        }

        if ($paymentMethod->operator_img) {
            Storage::disk('public')->delete('PaymentOperators/' . $paymentMethod->operator_img);
        }

        $paymentMethod->delete();
        return $paymentMethod;
    }

    public function getAllPaymentMethod()
    {
        $paymentMethods = PaymentMethod::with(['category', 'country'])
            ->get();
        return $paymentMethods;
    }

    public function getPaymentMethodDetail($paymentMethodId)
    {
        $paymentMethod = PaymentMethod::with(['category', 'country'])
            ->find($paymentMethodId);
        if (!$paymentMethod) {
            throw new AppException(
                "Payment Method Not Found",
                404,
                "Missing Method",
                "The Payment Method you are trying to update was not found. It may have been deleted."
            );
        }

        return $paymentMethod;
    }
}
