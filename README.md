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
