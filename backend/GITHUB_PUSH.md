# Push this backend to GitHub

Your backend is at `C:\Project\backend`, git is initialized, and the initial commit is done.

## 1. Create a new repository on GitHub

- Go to [https://github.com/new](https://github.com/new)
- Repository name: e.g. `corso-backend`
- Do **not** add a README, .gitignore, or license (they already exist here)
- Click **Create repository**

## 2. Add the remote and push

In a terminal (PowerShell or Command Prompt), run:

```powershell
cd C:\Project\backend
git remote add origin https://github.com/YOUR_USERNAME/YOUR_REPO_NAME.git
git push -u origin main
```

Replace `YOUR_USERNAME` with your GitHub username and `YOUR_REPO_NAME` with the repo name (e.g. `corso-backend`).

**Using SSH instead of HTTPS:**

```powershell
git remote add origin git@github.com:YOUR_USERNAME/YOUR_REPO_NAME.git
git push -u origin main
```

## 3. After the first push

- Future pushes: `git push`
- The `.env` file is **not** in the repo (it’s in `.gitignore`). On another machine or clone, copy `.env.example` to `.env` and set your database and JWT settings.
