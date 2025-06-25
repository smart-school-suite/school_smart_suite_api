# Docker Setup Instructions

## Prerequisites

1. **Docker Hub Account**: You need a Docker Hub account to push images
2. **GitHub Secrets**: Configure the following secrets in your GitHub repository:
   - `DOCKER_USERNAME`: Your Docker Hub username
   - `DOCKER_PASSWORD`: Your Docker Hub password or access token

## Setting up GitHub Secrets

1. Go to your GitHub repository
2. Navigate to Settings → Secrets and variables → Actions
3. Click "New repository secret" and add:
   - Name: `DOCKER_USERNAME`, Value: Your Docker Hub username
   - Name: `DOCKER_PASSWORD`, Value: Your Docker Hub password/token

## How it works

When you push to the `bill2` branch, the GitHub Actions workflow will:

1. Checkout the code
2. Set up Docker Buildx for multi-platform builds
3. Log in to Docker Hub using your credentials
4. Create the production .env file with your database credentials
5. Build the Docker image for both AMD64 and ARM64 architectures
6. Push the image to Docker Hub with appropriate tags

## Docker Image Tags

The workflow creates the following tags:
- `bill2` - Latest build from bill2 branch
- `bill2-<commit-sha>` - Specific commit build
- `latest` - If bill2 is the default branch

## Local Development

To run the application locally with Docker:

```bash
# Build and run with docker-compose
docker-compose up --build

# Or run just the build
docker build -t school-smart-suite-api .

# Run the container
docker run -p 8000:80 school-smart-suite-api
```

The application will be available at `http://localhost:8000`

## Production Deployment

To deploy the built image from Docker Hub:

```bash
# Pull the latest image
docker pull nyuydinebill/school-smart-suite-api:bill2

# Run the container
docker run -d -p 80:80 --name smart-suite nyuydinebill/school-smart-suite-api:bill2
```

## Environment Variables

The Docker image includes all the environment variables from your `.env` file:
- Database connection to srv504.hstgr.io
- Pusher configuration
- Mail settings
- Firebase credentials path

## Notes

- The Docker image uses PHP 8.3 with Apache
- Includes all Laravel optimizations (config cache, route cache, view cache)
- Runs migrations automatically on container start
- Uses the production database credentials from your .env file
- Supports multi-platform builds (AMD64 and ARM64)
