<?php
// הגדרת כותרות לקבלת נתונים בפורמט JSON
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// הגדרת כתובת האימייל שאליה יישלחו הלידים
// TODO: החליפו את הכתובת הזו באימייל שלכם
$to_email = "dan@leshem-it.co.il"; // ניתן לשנות לכל מייל שרוצים לקבל בו את הפניות

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // קריאת נתוני ה-JSON שנשלחו מהדפדפן
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);
    
    // שליפת הנתונים מהטופס
    $business_name = isset($data["businessName"]) ? strip_tags(trim($data["businessName"])) : '';
    $contact_name = isset($data["contactName"]) ? strip_tags(trim($data["contactName"])) : '';
    $contact_phone = isset($data["contactPhone"]) ? strip_tags(trim($data["contactPhone"])) : '';
    
    // בדיקת תקינות בסיסית
    if (empty($business_name) || empty($contact_name) || empty($contact_phone)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "אנא מלאו את כל השדות החשובים."]);
        exit;
    }
    
    // נושא המייל
    $subject = "=?UTF-8?B?" . base64_encode("ליד חדש מאתר האינטרנט: " . $business_name) . "?=";
    
    // תוכן המייל (HTML מעוצב)
    $message = "
    <html>
    <head>
        <title>פרטי ליד חדש</title>
        <style>
            body { font-family: Arial, sans-serif; direction: rtl; text-align: right; }
            .container { padding: 20px; border: 1px solid #ddd; border-radius: 8px; max-width: 600px; }
            h2 { color: #1f3d63; }
            p { font-size: 16px; line-height: 1.5; }
            .highlight { font-weight: bold; color: #0b132b; }
        </style>
    </head>
    <body>
        <div class='container'>
            <h2>התקבל ליד חדש מאתר האינטרנט של לשם שירותי מחשוב</h2>
            <p><span class='highlight'>שם העסק:</span> {$business_name}</p>
            <p><span class='highlight'>שם איש קשר:</span> {$contact_name}</p>
            <p><span class='highlight'>טלפון:</span> {$contact_phone}</p>
            <p><span class='highlight'>תאריך ושעה:</span> " . date("d/m/Y H:i:s") . "</p>
        </div>
    </body>
    </html>
    ";
    
    // הגדרת כותרות המייל (Headers)
    // הערה חשובה לשרתי גלקום (Galcomm Linux Hosting):
    // כדי למנוע את סינון המייל כספאם, כותרת ה-From צריכה להכיל אימייל מתוך הדומיין שלכם (למשל website@yourdomain.co.il)
    // מומלץ להחליף את הכתובת להלן בדומיין האמיתי לאחר רכישתו והעלאת האתר.
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From: website@leshem-it.co.il" . "\r\n"; 
    $headers .= "Reply-To: " . $to_email . "\r\n";
    $headers .= "X-Mailer: PHP/" . phpversion();
    
    // שליחת המייל באמצעות פונקציית mail המובנית בשרת
    if (mail($to_email, $subject, $message, $headers)) {
        echo json_encode(["success" => true, "message" => "ההודעה נשלחה בהצלחה!"]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "שליחת המייל נכשלה בשרת."]);
    }
} else {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "שיטת בקשה לא נתמכת."]);
}
?>
