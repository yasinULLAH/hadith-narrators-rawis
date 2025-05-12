# 📜 Isnad Narrators Explorer | اسناد راوی ایکسپلورر

ℹ️ **Description | تفصیل**
A single-file, offline-first web application designed for exploring Isnad (chain of narration) narrator data. It loads information from a local `all_rawis.csv` file into your browser's IndexedDB storage on the first run, allowing for fast, private, and offline access thereafter. No server interaction is involved, ensuring complete data privacy.

ایک سنگل فائل، آف لائن فرسٹ ویب ایپلیکیشن جو اسناد (سلسلہ روایت) کے راویوں کے ڈیٹا کو دریافت کرنے کے لیے ڈیزائن کی گئی ہے۔ یہ پہلی بار چلانے پر مقامی `all_rawis.csv` فائل سے معلومات آپ کے براؤزر کے IndexedDB اسٹوریج میں لوڈ کرتی ہے، جس سے بعد میں تیز، نجی اور آف لائن رسائی ممکن ہوتی ہے۔ اس میں کوئی سرور تعامل شامل نہیں ہے، جو ڈیٹا کی مکمل رازداری کو یقینی بناتا ہے۔

---

✨ **Features | خصوصیات**

*   💾 **CSV to IndexedDB Loading | CSV سے IndexedDB لوڈنگ:** Automatically parses and stores data from `all_rawis.csv` locally on first use. | پہلی بار استعمال پر `all_rawis.csv` سے ڈیٹا خود بخود پارس اور مقامی طور پر اسٹور کرتا ہے۔
*   🌐 **Responsive UI | ریسپانسیو UI:** Adapts to different screen sizes for usability on various devices. | مختلف آلات پر استعمال کے لیے مختلف اسکرین سائزز کے مطابق ڈھل جاتا ہے۔
*   🗣️ **Bilingual (English/Urdu) | دو لسانی (انگریزی/اردو):** Toggle between English and Urdu interface with full Right-to-Left (RTL) support for Urdu. | اردو کے لیے مکمل رائٹ ٹو لیفٹ (RTL) سپورٹ کے ساتھ انگریزی اور اردو انٹرفیس کے درمیان ٹوگل کریں۔
*   🎨 **Dark/Light Theme | ڈارک/لائٹ تھیم:** Switch between visual themes based on user preference. | صارف کی ترجیح کی بنیاد پر بصری تھیمز کے درمیان سوئچ کریں۔
*   🔍 **Search & Filter | تلاش اور فلٹر:** Powerful filtering options by Narrator Name, Grade, Tags, Category, and Bookmarked status. | راوی کا نام، درجہ، ٹیگز، زمرہ، اور بک مارک شدہ حیثیت کے لحاظ سے طاقتور فلٹرنگ کے اختیارات۔
*   📄 **Pagination | صفحہ بندی:** Efficiently handles large datasets by displaying narrator lists in manageable pages. | راویوں کی فہرستوں کو قابل انتظام صفحات میں دکھا کر بڑے ڈیٹاسیٹس کو مؤثر طریقے سے ہینڈل کرتا ہے۔
*   🖱️ **Clickable Relationships | کلک کے قابل تعلقات:** Easily navigate between related narrators (parents, children, teachers, students) by clicking their IDs in the details view. | تفصیلات کے منظر میں ان کی IDs پر کلک کرکے متعلقہ راویوں (والدین، اولاد، اساتذہ، تلامذہ) کے درمیان آسانی سے نیویگیٹ کریں۔
*   🔖 **Bookmarking | بک مارکنگ:** Save important or frequently accessed narrators for quick retrieval. | فوری بازیافت کے لیے اہم یا کثرت سے رسائی والے راویوں کو محفوظ کریں۔
*   📝 **Personal Notes | ذاتی نوٹس:** Add and save personal annotations or research notes specific to each narrator. | ہر راوی کے لیے مخصوص ذاتی تشریحات یا تحقیقی نوٹس شامل کریں اور محفوظ کریں۔
*   📊 **Graph Visualization | گراف ویژولائزیشن:** Interactively explore direct teacher-student relationships with zoom and pan controls. | زوم اور پین کنٹرولز کے ساتھ براہ راست استاد-شاگرد تعلقات کو انٹرایکٹو طور پر دریافت کریں۔
*   👣 **Relationship Path Tracing | تعلقات کا راستہ ٹریسنگ:** Find the shortest connection path between any two selected narrators based on teacher/student links. | استاد/شاگرد لنکس کی بنیاد پر کسی بھی دو منتخب راویوں کے درمیان مختصر ترین کنکشن کا راستہ تلاش کریں۔
*   📤 **JSON Export/Import | JSON ایکسپورٹ/امپورٹ:** Complete backup and restore functionality for all narrator data, bookmarks, and notes via JSON files. | JSON فائلوں کے ذریعے تمام راوی ڈیٹا، بک مارکس، اور نوٹس کے لیے مکمل بیک اپ اور بحالی کی فعالیت۔
*   🚫 **No Dependencies | کوئی انحصار نہیں:** Built purely with Vanilla JavaScript, HTML, and CSS. No external libraries needed. | خالص ونیلا جاوا اسکرپٹ، ایچ ٹی ایم ایل، اور سی ایس ایس کے ساتھ بنایا گیا ہے۔ کسی بیرونی لائبریری کی ضرورت نہیں۔
*   🖨️ **Graph Printing | گراف پرنٹنگ:** Option to print the current relationship graph visualization. | موجودہ تعلقات کے گراف ویژولائزیشن کو پرنٹ کرنے کا آپشن۔

---

🚀 **How to Use | استعمال کرنے کا طریقہ**

1.  **Download | ڈاؤن لوڈ:** Get the `isnad_explorer.html` file and the `all_rawis.csv` data file. | `isnad_explorer.html` فائل اور `all_rawis.csv` ڈیٹا فائل حاصل کریں۔
2.  **Place | رکھیں:** Ensure both files (`isnad_explorer.html` and `all_rawis.csv`) are located in the same directory/folder. | یقینی بنائیں کہ دونوں فائلیں (`isnad_explorer.html` اور `all_rawis.csv`) ایک ہی ڈائرکٹری/فولڈر میں موجود ہیں۔
3.  **Open | کھولیں:** Open the `isnad_explorer.html` file using a modern web browser (like Chrome, Firefox, Edge). | `isnad_explorer.html` فائل کو جدید ویب براؤزر (جیسے کروم، فائر فاکس، ایج) کا استعمال کرتے ہوئے کھولیں۔
4.  **First Load | پہلی لوڈ:** On the very first launch, the application will automatically load and process the data from `all_rawis.csv`. Please wait for this process to complete (a loading indicator will be shown). | پہلی بار لانچ پر، ایپلیکیشن خود بخود `all_rawis.csv` سے ڈیٹا لوڈ اور پراسیس کرے گی۔ براہ کرم اس عمل کے مکمل ہونے کا انتظار کریں (ایک لوڈنگ انڈیکیٹر دکھایا جائے گا)۔
5.  **Explore | ایکسپلور کریں:** Once loaded, use the interface to search, filter, view details, visualize relationships, and utilize other features. Subsequent loads will be much faster as data is read from IndexedDB. | لوڈ ہونے کے بعد، تلاش کرنے، فلٹر کرنے، تفصیلات دیکھنے، تعلقات کو ویژولائز کرنے، اور دیگر خصوصیات کو استعمال کرنے کے لیے انٹرفیس کا استعمال کریں۔ بعد میں لوڈز بہت تیز ہوں گے کیونکہ ڈیٹا IndexedDB سے پڑھا جاتا ہے۔

---

🛠️ **Technology Stack | ٹیکنالوجی اسٹیک**

*   HTML5
*   CSS3 (with CSS Variables | CSS متغیرات کے ساتھ)
*   Vanilla JavaScript (ES6+) | ونیلا جاوا اسکرپٹ (ES6+)
*   IndexedDB (Browser Storage | براؤزر اسٹوریج)

---

👤 **Author | مصنف**

*   Yasin Ullah (Pakistani | پاکستانی)

---

⚖️ **License | لائسنس**

This software is provided "as-is" without warranty of any kind. Use at your own risk. Consider adding a standard open-source license (like MIT) if you plan to share widely.

یہ سافٹ ویئر بغیر کسی قسم کی وارنٹی کے "جیسا ہے" فراہم کیا گیا ہے۔ اپنے خطرے پر استعمال کریں۔ اگر آپ وسیع پیمانے پر اشتراک کرنے کا ارادہ رکھتے ہیں تو ایک معیاری اوپن سورس لائسنس (جیسے MIT) شامل کرنے پر غور کریں۔



# Isnad Narrators Explorer

An ultra-modern, single-file web application for exploring the relationships between narrators of Hadith and Islamic history. Built with HTML, CSS, and JavaScript, utilizing IndexedDB for persistent data storage.

## Features | خصوصیات

- **Offline Access:** Data is stored locally using IndexedDB for offline use. | **آف لائن رسائی:** ڈیٹا مقامی طور پر IndexedDB میں محفوظ کیا جاتا ہے تاکہ آف لائن استعمال کیا جا سکے۔
- **Search & Filter:** Easily find narrators by name, grade, or tags. | **تلاش اور فلٹر:** نام، درجہ، یا ٹیگز کے ذریعے راویوں کو آسانی سے تلاش کریں۔
- **Interactive Visualization:** Visualize relationships (teachers, students) with an animated infographic-style view. | **انٹرایکٹو ویژولائزیشن:** متحرک انفوگرافک طرز کے منظر کے ساتھ تعلقات (اساتذہ، طلباء) کو تصور کریں۔
- **Personal Notes & Bookmarks:** Add your own notes and bookmark important narrators. | **ذاتی نوٹس اور بُک مارکس:** اپنے نوٹس شامل کریں اور اہم راویوں کو بُک مارک کریں۔
- **Categorization:** Organize narrators using custom categories. | **درجہ بندی:** اپنی مرضی کے مطابق زمرے استعمال کرکے راویوں کو منظم کریں۔
- **Data Backup & Restore:** Export and import all your data (narrators, notes, bookmarks, categories) as a JSON file. | **ڈیٹا بیک اپ اور بحالی:** اپنے تمام ڈیٹا (راوی، نوٹس، بُک مارکس، زمرے) کو JSON فائل کے طور پر ایکسپورٹ اور امپورٹ کریں۔
- **Language Toggle:** Switch between English and Urdu (RTL support). | **زبان تبدیل کریں:** انگریزی اور اردو کے درمیان سوئچ کریں (RTL سپورٹ کے ساتھ)۔
- **Theme Toggle:** Choose between light and dark themes. | **تھیم تبدیل کریں:** لائٹ اور ڈارک تھیمز کے درمیان انتخاب کریں۔
- **Responsive Design:** Works seamlessly on desktop and mobile devices. | **ریسپانسیو ڈیزائن:** ڈیسک ٹاپ اور موبائل آلات پر بغیر کسی رکاوٹ کے کام کرتا ہے۔
- **No Login Required:** All data is stored locally in your browser. | **لاگ ان کی ضرورت نہیں:** تمام ڈیٹا آپ کے براؤزر میں مقامی طور پر محفوظ کیا جاتا ہے۔
- **Special Treatment for Prophet Muhammad (saw):** Highlighted visually in the list and visualization. | **نبی کریم صلی اللہ علیہ وآلہ وسلم کے لیے خصوصی سلوک:** فہرست اور ویژولائزیشن میں بصری طور پر نمایاں کیا گیا ہے۔

## How to Use | استعمال کا طریقہ

1.  Download the `index.html` file and the `all_rawis.csv` file. | `index.html` فائل اور `all_rawis.csv` فائل ڈاؤن لوڈ کریں۔
2.  Place both files in the same directory. | دونوں فائلوں کو ایک ہی ڈائریکٹری میں رکھیں۔
3.  Open the `index.html` file in your web browser. | اپنے ویب براؤزر میں `index.html` فائل کھولیں۔
4.  The app will load the data from the CSV file (or from IndexedDB if previously loaded) and you can start exploring. | ایپ CSV فائل سے ڈیٹا لوڈ کرے گی (یا اگر پہلے سے لوڈ کیا گیا ہے تو IndexedDB سے) اور آپ ایکسپلور کرنا شروع کر سکتے ہیں۔

## Data Source | ڈیٹا کا ذریعہ

The initial narrator data is loaded from the `all_rawis.csv` file. This file should be present in the same directory as the `index.html` file. | ابتدائی راوی کا ڈیٹا `all_rawis.csv` فائل سے لوڈ کیا جاتا ہے۔ یہ فائل `index.html` فائل کی طرح اسی ڈائریکٹری میں موجود ہونی چاہیے۔

## Backup and Restore | بیک اپ اور بحالی

-   **Backup:** Click the "Backup Data" button to download a JSON file containing all narrators and your personal data (notes, bookmarks, categories). | **بیک اپ:** "Backup Data" بٹن پر کلک کریں تاکہ تمام راویوں اور آپ کے ذاتی ڈیٹا (نوٹس، بُک مارکس، زمرے) پر مشتمل JSON فائل ڈاؤن لوڈ ہو جائے۔
-   **Restore:** Click the "Restore Data" button and select a previously downloaded backup JSON file. **Warning:** This will overwrite all existing data in the app. | **بحالی:** "Restore Data" بٹن پر کلک کریں اور پہلے سے ڈاؤن لوڈ کی گئی بیک اپ JSON فائل منتخب کریں۔ **انتباہ:** یہ ایپ میں موجود تمام موجودہ ڈیٹا کو اوور رائٹ کر دے گا۔

## Disclaimer | دستبرداری

The data provided is for informational purposes only. Accuracy is aimed for, but not guaranteed. Always consult authoritative sources. | فراہم کردہ ڈیٹا صرف معلوماتی مقاصد کے لیے ہے۔ درستگی کا مقصد ہے، لیکن ضمانت نہیں ہے۔ ہمیشہ مستند ذرائع سے رجوع کریں۔

## Development | ترقی

This is a single-file application using pure HTML, CSS, and JavaScript. IndexedDB is used for client-side data persistence. The visualization is a basic SVG implementation. | یہ خالص HTML، CSS، اور JavaScript کا استعمال کرتے ہوئے ایک سنگل فائل ایپلی کیشن ہے۔ کلائنٹ سائیڈ ڈیٹا کی پائیداری کے لیے IndexedDB استعمال کیا جاتا ہے۔ ویژولائزیشن ایک بنیادی SVG امپلیمنٹیشن ہے۔

## Author | مصنف

Yasin Ullah (Pakistan)


**Title:** Explore Isnad Narrators Offline with "Isnad Narrators Explorer" Web App! | **عنوان:** "اسناد راوی ایکسپلورر" ویب ایپ کے ساتھ آف لائن اسناد راویوں کو دریافت کریں!

Assalamu alaikum everyone, | السلام علیکم سب کو،

I'm excited to share a small web application I've developed called "Isnad Narrators Explorer". | میں ایک چھوٹی ویب ایپلی کیشن شیئر کرنے کے لیے پرجوش ہوں جسے میں نے "اسناد راوی ایکسپلورر" کے نام سے تیار کیا ہے۔

This is a single-file HTML app that runs entirely in your browser, allowing you to explore a dataset of Hadith and Islamic history narrators offline. | یہ ایک سنگل فائل HTML ایپ ہے جو مکمل طور پر آپ کے براؤزر میں چلتی ہے، جس سے آپ حدیث اور اسلامی تاریخ کے راویوں کے ڈیٹا سیٹ کو آف لائن دریافت کر سکتے ہیں۔

**Key Features:** | **اہم خصوصیات:**

-   🔍 Search and filter narrators. | 🔍 راویوں کو تلاش اور فلٹر کریں۔
-   🌳 Visualize relationships (teachers, students) in a simple infographic style. | 🌳 ایک سادہ انفوگرافک انداز میں تعلقات (اساتذہ، طلباء) کو تصور کریں۔
-   📝 Add your own notes and bookmarks. | 📝 اپنے نوٹس اور بُک مارکس شامل کریں۔
-   📂 Categorize narrators for better organization. | 📂 بہتر تنظیم کے لیے راویوں کو درجہ بندی کریں۔
-   💾 Full data backup and restore functionality. | 💾 مکمل ڈیٹا بیک اپ اور بحالی کی فعالیت۔
-   🌐 Language toggle (English | اردو) and Theme toggle (Light | Dark). | 🌐 زبان تبدیل کریں (انگریزی | اردو) اور تھیم تبدیل کریں (لائٹ | ڈارک)۔
-   ✨ Special visual treatment for our beloved Prophet Muhammad (saw). | ✨ ہمارے پیارے نبی کریم صلی اللہ علیہ وآلہ وسلم کے لیے خصوصی بصری سلوک۔

**How it works:** | **یہ کیسے کام کرتا ہے:**

You just need to download the `index.html` file and the `all_rawis.csv` data file and open the HTML file in your browser. The app uses your browser's local storage (IndexedDB) to save the data and your personal additions. | آپ کو صرف `index.html` فائل اور `all_rawis.csv` ڈیٹا فائل ڈاؤن لوڈ کرنے اور اپنے براؤزر میں HTML فائل کھولنے کی ضرورت ہے۔ ایپ آپ کے براؤزر کے مقامی اسٹوریج (IndexedDB) کا استعمال کرتی ہے تاکہ ڈیٹا اور آپ کے ذاتی اضافے کو محفوظ کیا جا سکے۔

This is a personal project aimed at providing a simple, offline tool for exploring this important historical data. | یہ ایک ذاتی پروجیکٹ ہے جس کا مقصد اس اہم تاریخی ڈیٹا کو دریافت کرنے کے لیے ایک سادہ، آف لائن ٹول فراہم کرنا ہے۔

Feel free to download and use it. Feedback is welcome! | اسے ڈاؤن لوڈ اور استعمال کرنے کے لیے آزاد محسوس کریں۔ تاثرات کا خیرمقدم ہے!

JazakAllah Khair. | جزاک اللہ خیر۔
