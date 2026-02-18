# Backend in this repo

The CodeIgniter 4 backend lives in the **`backend/`** folder of this repository (same repo as the frontend).

- **Frontend** (Vercel): deploy from repo root; Vercel uses your frontend build (e.g. `build` or `public`). The `backend/` folder is ignored by the frontend build.
- **Backend** (PHP host): copy or deploy the `backend/` folder to a PHP server (XAMPP, shared hosting, etc.). Add a `.env` file there (see `backend/README.md` and `backend/SETUP.md`).
