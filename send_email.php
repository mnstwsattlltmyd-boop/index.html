<?php

// 1. تضمين مكتبة PHPMailer
// يجب تعديل هذا المسار ليتناسب مع موقع تثبيت مكتبة PHPMailer على الخادم الخاص بك
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

// 2. إعداد المتغيرات الأساسية
$targetEmail = 'alhmyryw65@gmail.com'; // البريد الإلكتروني الذي سيستقبل الطلبات
$platformNumber = '966576735113';       // رقم المنصة الثابت للواتساب (بدون +)

// التحقق من أن الطلب تم إرساله بطريقة POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 3. استقبال البيانات من نموذج HTML
    $requesterName = htmlspecialchars($_POST['requester_name'] ?? 'غير متوفر');
    $requesterId = htmlspecialchars($_POST['requester_id'] ?? 'غير متوفر');
    $providerName = htmlspecialchars($_POST['provider_name'] ?? 'غير متوفر');
    $providerId = htmlspecialchars($_POST['provider_id'] ?? 'غير متوفر');
    $agreementDetails = htmlspecialchars($_POST['agreement_details'] ?? 'غير متوفر');
    $baptismPeriod = htmlspecialchars($_POST['baptism_period'] ?? 'غير متوفر');
    $baptismEndDate = htmlspecialchars($_POST['baptism_end_date'] ?? 'غير متوفر');
    $servicePrice = htmlspecialchars($_POST['service_price'] ?? 'غير متوفر');
    $paymentMethod = htmlspecialchars($_POST['paymentMethod'] ?? 'لم يُحدد');

    // 4. تنسيق محتوى البريد الإلكتروني (نص عادي HTML)
    $emailSubject = 'طلب فتح تعميد جديد - ' . $requesterName;
    
    $emailBody = "<h1>طلب فتح تعميد جديد من منصة واسطة</h1>";
    $emailBody .= "<p><strong>تاريخ ووقت الإرسال:</strong> " . date("Y-m-d H:i:s") . "</p>";
    $emailBody .= "<hr>";

    $emailBody .= "<h2>1- بيانات صاحب الطلب:</h2>";
    $emailBody .= "<ul>";
    $emailBody .= "<li><strong>الاسم:</strong> {$requesterName}</li>";
    $emailBody .= "<li><strong>الرقم:</strong> {$requesterId}</li>";
    $emailBody .= "</ul>";

    $emailBody .= "<h2>2- بيانات مقدم الخدمة:</h2>";
    $emailBody .= "<ul>";
    $emailBody .= "<li><strong>الاسم:</strong> {$providerName}</li>";
    $emailBody .= "<li><strong>الرقم:</strong> {$providerId}</li>";
    $emailBody .= "</ul>";

    $emailBody .= "<h2>3- تفاصيل الاتفاق:</h2>";
    $emailBody .= "<ul>";
    $emailBody .= "<li><strong>تفاصيل الاتفاق:</strong> <br>{$agreementDetails}</li>";
    $emailBody .= "<li><strong>مدة التعميد:</strong> {$baptismPeriod} يوم</li>";
    $emailBody .= "<li><strong>يوم الانتهاء:</strong> {$baptismEndDate}</li>";
    $emailBody .= "</ul>";
    
    $emailBody .= "<h2>4- تفاصيل الدفع:</h2>";
    $emailBody .= "<ul>";
    $emailBody .= "<li><strong>مبلغ التعميد:</strong> {$servicePrice} ريال سعودي</li>";
    $emailBody .= "<li><strong>طريقة الدفع:</strong> {$paymentMethod}</li>";
    $emailBody .= "</ul>";
    
    $emailBody .= "<p style='color:green;'>تمت الموافقة على الشروط والأحكام.</p>";


    // 5. تهيئة PHPMailer وإعداد الإرسال
    $mail = new PHPMailer(true);
    
    try {
        // إعدادات الخادم (SMTP)
        $mail->isSMTP();                                       
        $mail->Host       = 'YOUR_SMTP_HOST'; // مثال: 'smtp.gmail.com' أو 'mail.yourdomain.com'
        $mail->SMTPAuth   = true;                                 
        $mail->Username   = 'YOUR_SMTP_USERNAME';             // اسم مستخدم SMTP (البريد الذي سيرسل منه)
        $mail->Password   = 'YOUR_SMTP_PASSWORD';             // كلمة مرور SMTP
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;      // أو PHPMailer::ENCRYPTION_STARTTLS
        $mail->Port       = 465;                               // أو 587 إذا استخدمت STARTTLS
        $mail->CharSet    = 'UTF-8';                           // دعم اللغة العربية

        // إعدادات المستلمين والمحتوى
        $mail->setFrom('YOUR_SENDER_EMAIL', 'منصة واسطة - طلب جديد');
        $mail->addAddress($targetEmail, 'مسؤول المنصة'); // الإيميل الذي سيستقبل الطلب
        
        $mail->isHTML(true);                                  
        $mail->Subject = '=?UTF-8?B?'.base64_encode($emailSubject).'?='; // ترميز الموضوع
        $mail->Body    = $emailBody;
        $mail->AltBody = strip_tags($emailBody); // نسخة نصية بديلة
        
        // إرسال الإيميل التلقائي
        $mail->send();
        
        // 6. تجهيز رسالة الواتساب وإعادة التوجيه
        $whatsappMessage = "مرحباً، تم إرسال طلب تعميد جديد بنجاح. يرجى إنشاء قروب التعميد.\n\n";
        $whatsappMessage .= "*العميل:* {$requesterName} ({$requesterId})\n";
        $whatsappMessage .= "*مقدم الخدمة:* {$providerName} ({$providerId})\n";
        $whatsappMessage .= "*المبلغ:* {$servicePrice} ر.س";
        
        $whatsappLink = "https://wa.me/{$platformNumber}?text=" . urlencode($whatsappMessage);
        
        // 7. إعادة التوجيه إلى رابط الواتساب والانتهاء
        header("Location: " . $whatsappLink);
        exit();

    } catch (Exception $e) {
        // في حال فشل الإرسال (على الخادم)
        // يمكن أن تعيد المستخدم لصفحة خطأ أو تعرض رسالة
        echo "عذراً، فشل إرسال الطلب عبر البريد الإلكتروني. يرجى المحاولة لاحقاً. خطأ PHPMailer: {$mail->ErrorInfo}";
    }

} else {
    // إذا لم يتم إرسال الطلب بطريقة POST (دخول مباشر للملف)
    header("Location: index.html"); // أو اسم صفحة النموذج الرئيسية
    exit();
}
?>
