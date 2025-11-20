FROM node:18-alpine

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install

# Copy proxy script
COPY proxy.js .

# Expose port
EXPOSE 8080

# Run proxy
CMD ["node", "proxy.js"]
