<?php
// 1. إعداد هيدر JSON للإشارة إلى أن الاستجابة ستكون بصيغة JSON
header('Content-Type: application/json');

// 2. عنوان البريد الإلكتروني المستهدف الذي سيستقبل الطلبات
$targetEmail = "alhmyryw65@gmail.com"; 

// 3. بريد المرسل (ضروري لبعض السيرفرات لضمان وصول الإيميل)
// تأكد من استبدال yourplatformdomain.com بنطاق موقعك الحقيقي
$fromEmail = "no-reply@yourplatformdomain.com"; 

// 4. التحقق من أن الطلب من نوع POST وأن البيانات موجودة
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST)) {
    
    // 5. جمع البيانات المرسلة عبر نموذج FormData
    $requesterName = htmlspecialchars($_POST['requesterName'] ?? '');
    $requesterId = htmlspecialchars($_POST['requesterId'] ?? '');
    $providerName = htmlspecialchars($_POST['providerName'] ?? '');
    $providerId = htmlspecialchars($_POST['providerId'] ?? '');
    $agreementDetails = htmlspecialchars($_POST['agreementDetails'] ?? '');
    $baptismPeriod = htmlspecialchars($_POST['baptismPeriod'] ?? '');
    $baptismEndDate = htmlspecialchars($_POST['baptismEndDate'] ?? '');
    $servicePrice = htmlspecialchars($_POST['servicePrice'] ?? '');
    $paymentMethod = htmlspecialchars($_POST['paymentMethod'] ?? 'لم يُحدد');
    
    // 6. بناء محتوى الرسالة
    $subject = "طلب فتح تعميد جديد - " . $requesterName;
    
    $message = "طلب فتح تعميد جديد من منصة واسطة\n\n";
    $message .= "*1- بيانات صاحب الطلب:*\nالاسم: {$requesterName}\nالرقم: {$requesterId}\n\n";
    $message .= "*2- بيانات مقدم الخدمة:*\nالاسم: {$providerName}\nالرقم: {$providerId}\n\n";
    $message .= "*3- تفاصيل الاتفاق:*\nتفاصيل الاتفاق:\n{$agreementDetails}\nمدة التعميد: {$baptismPeriod} يوم\nيوم الانتهاء: {$baptismEndDate}\n\n";
    $message .= "*4- تفاصيل الدفع:*\nمبلغ التعميد: {$servicePrice} ريال سعودي\nطريقة الدفع: {$paymentMethod}\n\n";
    $message .= "*أرقام التواصل لإنشاء قروب الواتساب:*\nرقم صاحب الطلب: {$requesterId}\nرقم مقدم الخدمة: {$providerId}\n";
    $message .= "تمت الموافقة على الشروط والأحكام.";
    
    // 7. إعداد الهيدرات لضمان إرسال النص بصيغة UTF-8 (لدعم اللغة العربية)
    $headers = "From: " . $fromEmail . "\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    
    // 8. محاولة إرسال البريد الإلكتروني باستخدام دالة mail() الخاصة بـ PHP
    if (mail($targetEmail, $subject, $message, $headers)) {
        // الإرسال نجح
        echo json_encode(['success' => true, 'message' => 'تم إرسال الطلب بنجاح.']);
    } else {
        // الإرسال فشل
        echo json_encode(['success' => false, 'message' => 'فشل في إرسال البريد الإلكتروني. (تحقق من إعدادات mail() على الخادم).']);
    }
    
} else {
    // الطلب غير صحيح (لم يتم إرساله عبر النموذج)
    http_response_code(400); // Bad Request
    echo json_encode(['success' => false, 'message' => 'الطلب غير صالح.']);
}
?>
