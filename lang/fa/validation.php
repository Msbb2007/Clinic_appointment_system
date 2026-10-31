<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'accepted'        => 'فیلد :attribute باید پذیرفته شود.',
    'active_url'      => 'فیلد :attribute یک وب‌سایت معتبر نیست.',
    'after'           => 'فیلد :attribute باید تاریخی بعد از :date باشد.',
    'alpha'           => 'فیلد :attribute فقط باید شامل حروف الفبا باشد.',
    'alpha_dash'      => 'فیلد :attribute فقط باید شامل حروف، اعداد، خط تیره و زیرخط باشد.',
    'alpha_num'       => 'فیلد :attribute فقط باید شامل حروف و اعداد باشد.',
    'array'           => 'فیلد :attribute باید یک آرایه باشد.',
    'confirmed'       => 'تاییدیه فیلد :attribute مطابقت ندارد.',
    'email'           => 'فرمت فیلد :attribute معتبر نیست.',
    'exists'          => ':attribute انتخاب شده معتبر نیست.',
    'image'           => 'فیلد :attribute باید یک تصویر باشد.',
    'integer'         => 'فیلد :attribute باید یک عدد صحیح باشد.',
    'max'             => [
        'numeric' => 'فیلد :attribute نباید بزرگتر از :max باشد.',
        'file'    => 'حجم فایل :attribute نباید بیشتر از :max کیلوبایت باشد.',
        'string'  => 'فیلد :attribute نباید بیشتر از :max کاراکتر باشد.',
        'array'   => 'فیلد :attribute نباید بیشتر از :max آیتم باشد.',
    ],
    'min'             => [
        'numeric' => 'فیلد :attribute نباید کوچکتر از :min باشد.',
        'file'    => 'حجم فایل :attribute حداقل باید :min کیلوبایت باشد.',
        'string'  => 'فیلد :attribute نباید کمتر از :min کاراکتر باشد.',
        'array'   => 'فیلد :attribute حداقل باید شامل :min آیتم باشد.',
    ],
    'numeric'         => 'فیلد :attribute باید یک عدد باشد.',
    'required'        => 'وارد کردن فیلد :attribute الزامی است.',
    'string'          => 'فیلد :attribute باید از نوع متن باشد.',
    'unique'          => ':attribute قبلاً در سیستم ثبت شده است.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes (ترجمه نام فیلدها)
    |--------------------------------------------------------------------------
    */
    'attributes' => [
        'full_name'    => 'نام و نام خانوادگی',
        'email'        => 'ایمیل',
        'phone'        => 'شماره تماس',
        'password'     => 'کلمه عبور',
        'specialty_id' => 'تخصص پزشک',
        'appointment_date' => 'تاریخ نوبت',
        'role' => 'نقش کاربر',
        'medical_license_number' => 'کد تظام پزشکی',
        'bio' => 'بیوگرافی',
        'consultation_fee' => 'هزینه ویزیت',
        'verification_status' => 'وضعییت تایید'
    ],

];
