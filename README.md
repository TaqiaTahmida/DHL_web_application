A full-stack, automated solution for processing and tracking DHL incidents using AI.

## 🏗️ Architecture
*   **Frontend (Vue.js):** Modern web dashboard to view, upload, and manage incidents.
*   **Backend (PHP/MySQL):** Secure REST API handling JWT authentication, data storage, and Gemini AI processing.
*   **RPA (UiPath):** Automated robot that monitors Google Drive, extracts text from files, and pushes data to the web app.

## 🚀 How It Works
1. Drop a raw text or PDF incident report into the designated Google Drive folder.
2. The **UiPath Robot** automatically downloads and extracts the text.
3. The robot securely posts the text to the PHP backend.
4. The backend uses **Google Gemini 2.5 Flash** to intelligently convert the unstructured text into a categorized JSON object.
5. The incident instantly appears on your Vue web dashboard!

## ⚙️ Quick Setup
1. **Database:** Import `backend/init.sql` into MySQL.
2. **Backend:** Host the `backend` folder on XAMPP (Apache). Add your Gemini API key and DB credentials to `config.php`.
3. **Frontend:** Go to `frontend`, run `npm install`, then `npm run dev`.
4. **RPA:** Open `Main.xaml` in UiPath Studio, authenticate the Google Drive connection, create a folder locally for the robot to download files and run the file.
5. Ensure Gemini API is configured on UiPath Studio, as well as the login credentials for this system.
