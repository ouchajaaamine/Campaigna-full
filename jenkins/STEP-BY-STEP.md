# 🎯 Step-by-Step: Create Your First Pipeline in Blue Ocean

## 🌐 Step 1: Open Blue Ocean

1. Open your browser
2. Go to: **http://localhost:8080/blue**
3. You'll see the Blue Ocean welcome screen

**What you'll see:**
- A clean, modern blue interface
- "Welcome to Jenkins" message
- A button that says **"Create a new Pipeline"**

---

## 📝 Step 2: Create Pipeline

### Click the "Create a new Pipeline" button

You'll see options:
- Git
- GitHub
- Bitbucket

**Choose: Git** (simplest option, no token needed)

---

## 🔗 Step 3: Enter Your Repository URL

A text box will appear asking "Where do you store your code?"

**Enter this EXACT URL:**
```
https://github.com/ouchajaaamine/Campaigna-full
```

**Then click: "Create Pipeline"**

---

## ⚙️ Step 4: Watch Jenkins Work

Jenkins will now:
1. ✅ Connect to your repository
2. ✅ Scan for branches (finds `main`)
3. ✅ Look for `Jenkinsfile` (finds it!)
4. ✅ Automatically start your first build

**You'll see:**
- A loading animation
- "Scanning repository..."
- Then your pipeline will appear!

---

## 🎨 Step 5: See Your Pipeline Running

Your screen will show:

```
┌──────────────┐      ┌─────────────────┐
│  Checkout    │ ---> │ Build Frontend  │
│   (blue)     │      │    (blue)       │
└──────────────┘      └─────────────────┘
```

**Colors mean:**
- 🔵 Blue = Running right now
- 🟢 Green = Completed successfully
- 🔴 Red = Failed
- ⚪ Gray = Not started yet

---

## 📊 Step 6: View Build Details

**Click on any stage box** to see:
- Real-time logs
- What commands are running
- Output from npm install, npm build, etc.

**Example of what you'll see:**
```
📦 Checking out code...
Cloning into '/var/jenkins_home/workspace/...'

🔨 Building frontend...
npm install
added 234 packages...
npm run build
Creating an optimized production build...
✓ Compiled successfully

✅ Build completed successfully!
```

---

## 🎉 Step 7: Success!

When everything turns **green**, you'll see:
- ✅ Both stages completed
- Build time (e.g., "2m 34s")
- "Build #1 succeeded"

---

## 🔄 Step 8: Run Again (Optional)

Want to run another build?

1. Click the **"Run"** button (top right)
2. Watch it build again
3. See the new build appear below build #1

---

## 📸 Visual Guide

### What Your Screen Looks Like:

**1. Pipeline List (Main View):**
```
╔════════════════════════════════════════╗
║  Pipelines                              ║
║  ┌────────────────────────────────┐    ║
║  │ Campaigna-full                 │    ║
║  │ ● main                         │    ║
║  │ ✅ #1 - 2m 34s ago            │    ║
║  └────────────────────────────────┘    ║
╚════════════════════════════════════════╝
```

**2. Pipeline Detail (Click on pipeline):**
```
╔════════════════════════════════════════╗
║  Campaigna-full / main                 ║
║  ┌──────────┐  ┌──────────────────┐   ║
║  │ Checkout │─→│ Build Frontend   │   ║
║  │    ✅    │  │       ✅         │   ║
║  └──────────┘  └──────────────────┘   ║
║                                        ║
║  Build #1 succeeded (2m 34s)          ║
╚════════════════════════════════════════╝
```

**3. Stage Logs (Click on a stage):**
```
╔════════════════════════════════════════╗
║  Build Frontend                        ║
║  ════════════════════════════════════  ║
║  🔨 Building frontend...               ║
║  + npm install                         ║
║  added 234 packages in 45s            ║
║  + npm run build                       ║
║  ✓ Compiled successfully               ║
║  ════════════════════════════════════  ║
║  Duration: 1m 23s                      ║
╚════════════════════════════════════════╝
```

---

## ❓ Troubleshooting

### "Can't connect to repository"
- Check your internet connection
- Make sure the URL is exactly: `https://github.com/ouchajaaamine/Campaigna-full`

### "No Jenkinsfile found"
- Your repo already has a Jenkinsfile, this shouldn't happen
- If it does, make sure you're on the `main` branch

### Build fails on "Build Frontend"
- This is normal the first time (npm needs to download packages)
- Click the stage to see detailed logs
- Check if Node.js container downloaded successfully

### Blue Ocean page is blank
- Wait 30 seconds and refresh
- Make sure Jenkins is running: `docker ps` (should see `jenkins` container)

---

## 🎯 Quick Commands Reference

### If Jenkins isn't running:
```powershell
cd C:\Users\pc\Desktop\Campaigna-full\jenkins
docker-compose up -d
```

### Check Jenkins status:
```powershell
docker ps
# Look for container named "jenkins" with status "Up"
```

### View Jenkins logs:
```powershell
docker logs jenkins --tail 50
```

### Restart Jenkins:
```powershell
docker-compose restart
```

---

## ✅ Success Checklist

After following these steps, you should have:

- [ ] Blue Ocean interface open at http://localhost:8080/blue
- [ ] A pipeline called "Campaigna-full"
- [ ] At least one successful build (green checkmarks)
- [ ] Ability to click stages and see logs

---

## 🚀 Next Level (After First Build Works)

Once your first build succeeds, you can:

1. **Auto-trigger builds**: Set up webhooks so builds run on every push
2. **Add backend tests**: Add a stage for PHPStan and PHPUnit
3. **Deploy automatically**: Add deployment stage to push to server
4. **Notifications**: Get Slack/email when builds fail

But for now, just get that first green build! 🎉

---

**Ready? Open http://localhost:8080/blue and click "Create a new Pipeline"!**
