#!/bin/bash
set -e

# KAM (Krieger Auction Manager) Deployment Script
# Deploys to chuckycastle-apps instance alongside other applications

SERVER="ubuntu@44.211.71.114"
SSH_KEY="~/.ssh/LightsailDefaultKey-us-east-1.pem"
APP_DIR="/opt/applications/kam"
APP_PORT=3002
DOMAIN="kam.chuckycastle.io"

echo "Deploying KAM (Krieger Auction Manager)..."

# Step 1: Build locally
echo "Building application..."
npm run build

# Step 2: Create application directory on server
echo "Creating application directory..."
ssh -i "$SSH_KEY" "$SERVER" "sudo mkdir -p $APP_DIR && sudo chown -R ubuntu:ubuntu $APP_DIR"

# Step 3: Deploy application files
echo "Deploying application files..."
rsync -avz -e "ssh -i $SSH_KEY" \
  --exclude='node_modules' \
  --exclude='.git' \
  --exclude='legacy' \
  --exclude='coverage' \
  --exclude='.env' \
  --exclude='.env.local' \
  dist/ \
  package.json \
  package-lock.json \
  prisma/ \
  views/ \
  public/ \
  "$SERVER:$APP_DIR/"

# Step 4: Setup Node.js environment and install dependencies
echo "Installing dependencies on server..."
ssh -i "$SSH_KEY" "$SERVER" << 'ENDSSH'
cd /opt/applications/kam

# Install production dependencies
npm ci --only=production

# Generate Prisma client
npx prisma generate

# Run database migrations
npx prisma migrate deploy || echo "No migrations to run or migration failed"

ENDSSH

# Step 5: Create .env file template if it doesn't exist
echo "Checking environment configuration..."
ssh -i "$SSH_KEY" "$SERVER" << 'ENDSSH'
cd /opt/applications/kam

if [ ! -f .env ]; then
  cat > .env << 'EOF'
NODE_ENV=production
PORT=3002
HOST=0.0.0.0

# Database (Supabase)
DATABASE_URL=postgresql://postgres:password@db.utdejlclmmezhzbjpglj.supabase.co:5432/postgres
SUPABASE_URL=https://utdejlclmmezhzbjpglj.supabase.co
SUPABASE_ANON_KEY=your-anon-key

# Session & Security
SESSION_SECRET=change-this-to-a-secure-random-string-min-32-chars

# Application URL
APP_URL=https://kam.chuckycastle.io
EOF
  echo ".env file created. Update with production values!"
fi
ENDSSH

# Step 6: Install PM2 service
echo "Setting up PM2 process..."
ssh -i "$SSH_KEY" "$SERVER" << ENDSSH
cd /opt/applications/kam

# Stop existing process if running
pm2 stop kam 2>/dev/null || true
pm2 delete kam 2>/dev/null || true

# Start with PM2
pm2 start dist/server.js --name kam --env production

# Save PM2 process list
pm2 save

# Ensure PM2 starts on boot
pm2 startup systemd -u ubuntu --hp /home/ubuntu 2>/dev/null || true
ENDSSH

# Step 7: Configure Nginx
echo "Configuring nginx..."
ssh -i "$SSH_KEY" "$SERVER" "sudo tee /etc/nginx/sites-available/kam > /dev/null" << 'NGINXCONF'
# KAM - Krieger Auction Manager (Port 3002)
server {
    server_name kam.chuckycastle.io;

    # Security headers
    add_header X-Content-Type-Options nosniff;
    add_header X-Frame-Options DENY;
    add_header X-XSS-Protection "1; mode=block";
    add_header Referrer-Policy strict-origin-when-cross-origin;
    add_header Content-Security-Policy "default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self';";

    # Health check endpoint
    location /health {
        proxy_pass http://127.0.0.1:3002;
        proxy_http_version 1.1;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        access_log off;
    }

    # API routes
    location /api/ {
        proxy_pass http://127.0.0.1:3002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_set_header X-Forwarded-Port $server_port;
        proxy_cache_bypass $http_upgrade;
        proxy_hide_header X-Powered-By;
    }

    # Static assets with caching
    location /public/ {
        alias /opt/applications/kam/public/;
        add_header Cache-Control "public, max-age=31536000, immutable";
        try_files $uri =404;
    }

    # All other routes go to Express (server-rendered EJS templates)
    location / {
        proxy_pass http://127.0.0.1:3002;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
        proxy_cache_bypass $http_upgrade;
        add_header Cache-Control "no-cache, no-store, must-revalidate";
    }

    listen 443 ssl;
    ssl_certificate /etc/letsencrypt/live/chuckycastle.io/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/chuckycastle.io/privkey.pem;
    include /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem;
}

# HTTP to HTTPS redirect
server {
    if ($host = kam.chuckycastle.io) {
        return 301 https://$host$request_uri;
    }
    listen 80;
    server_name kam.chuckycastle.io;
    return 404;
}
NGINXCONF

# Enable site and reload nginx
ssh -i "$SSH_KEY" "$SERVER" "sudo ln -sf /etc/nginx/sites-available/kam /etc/nginx/sites-enabled/ && \
  sudo nginx -t && \
  sudo systemctl reload nginx"

# Step 8: Health check
echo "Running health check..."
sleep 3
ssh -i "$SSH_KEY" "$SERVER" "curl -f http://localhost:3002/health" || echo "Health check failed - check logs with: pm2 logs kam"

echo ""
echo "Deployment complete!"
echo ""
echo "Next steps:"
echo "1. Add DNS record for kam.chuckycastle.io pointing to 44.211.71.114"
echo "2. Update .env file on server with production Supabase credentials"
echo "3. Run certbot if SSL not configured: sudo certbot --nginx -d kam.chuckycastle.io"
echo "4. Check logs: ssh -i $SSH_KEY $SERVER 'pm2 logs kam'"
echo ""
echo "Application available at: https://kam.chuckycastle.io"
