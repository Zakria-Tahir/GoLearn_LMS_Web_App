# GoLearn Web LMS

GoLearn is a self-hosted Learning Management System (LMS) built in PHP. It allows users to create and manage their own courses, upload content, assign quizzes and assignments, and track grades — all through a clean web interface.

---

## 🚀 Features

- User-generated courses with categories and subcategories  
- Assignments and quizzes per course  
- Auto/manual grading  
- Upload support for course materials  
- Admin panel for managing the platform  

---

## ⚙️ Local Setup (XAMPP)

Follow the steps below to set up and run the project locally:

### 1. Clone the Repository

```bash
git clone https://github.com/Mian-JunaidBabar/GoLearn-LMS-Web-App.git
````

### 2. Move the Project Folder

Copy the cloned folder to your XAMPP `htdocs` directory:

```
C:\xampp\htdocs\golearn-web
```

### 3. Start XAMPP

* Open **XAMPP Control Panel**
* Start **Apache** and **MySQL**

### 4. Create the Database

* Go to [http://localhost/phpmyadmin](http://localhost/phpmyadmin)

* Click **New** and create a database named: `LMS`

* Go to the **Import** tab

* Select the SQL file located at:

  ```
  lms.sql
  ```

* Click **Go** to import the sample data and schema

### 5. Run the App

Visit the app in your browser:

```
http://localhost/golearn-web
```

---

## 🔐 Admin Panel Access

You can access the admin dashboard using:

* **URL**: `http://localhost/golearn-web/admin`
* **Username**: `admin`
* **Password**: `admin`

---

## 📁 Folder Structure

```
/golearn-web
│
├── /admin/         # Admin panel files
│   └── /utils/     # Admin-side backend handlers
│
├── /utils/         # Core backend PHP scripts (DB, logic, etc.)
│
├── /uploads/       # Uploaded files (assignments, images, etc.)
│
├── /extras/        # Contains lms.sql for DB import
│
├── index.php       # Main entry point
├── README.md       # Project documentation
└── ...             # All other core files
```

---

## 📌 Notes

* Make sure the `mysqli` extension is enabled in your PHP config
* The `uploads/` folder should have write permissions
* Compress large image files before uploading to improve performance

---

## 🛠️ Contributions

Feel free to fork and improve! Submit pull requests or issues if you’d like to contribute or suggest changes.
