// Service Worker for L9 Fitness Offline Support
// Handles caching of plans and provides offline functionality

const CACHE_NAME = 'l9-fitness-offline-v1';
const OFFLINE_URL = '/Capstone-latest/public/offline_plans.html';

// Files to cache for offline use
const CACHE_URLS = [
  '/Capstone-latest/public/offline_plans.html',
  '/Capstone-latest/public/assets/css/main.css',
  '/Capstone-latest/public/assets/css/offline.css',
  '/Capstone-latest/public/assets/js/offline-plans.js',
  'https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css',
  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css'
];

// Install event - cache essential files
self.addEventListener('install', event => {
  console.log('L9 Fitness Service Worker installing...');
  
  event.waitUntil(
    caches.open(CACHE_NAME)
      .then(cache => {
        console.log('Caching offline files...');
        return cache.addAll(CACHE_URLS);
      })
      .then(() => {
        console.log('All files cached successfully');
        return self.skipWaiting();
      })
      .catch(error => {
        console.error('Failed to cache files:', error);
      })
  );
});

// Activate event - clean up old caches
self.addEventListener('activate', event => {
  console.log('L9 Fitness Service Worker activating...');
  
  event.waitUntil(
    caches.keys().then(cacheNames => {
      return Promise.all(
        cacheNames.map(cacheName => {
          if (cacheName !== CACHE_NAME) {
            console.log('Deleting old cache:', cacheName);
            return caches.delete(cacheName);
          }
        })
      );
    }).then(() => {
      return self.clients.claim();
    })
  );
});

// Fetch event - handle requests with cache-first strategy for offline support
self.addEventListener('fetch', event => {
  const url = new URL(event.request.url);
  
  // Handle API requests for plans data
  if (url.pathname.includes('/api/offline_plans.php')) {
    event.respondWith(
      fetch(event.request)
        .then(response => {
          // If online, cache the response and return it
          if (response.ok) {
            const responseClone = response.clone();
            caches.open(CACHE_NAME).then(cache => {
              cache.put(event.request, responseClone);
            });
          }
          return response;
        })
        .catch(() => {
          // If offline, try to serve from cache
          return caches.match(event.request)
            .then(cachedResponse => {
              if (cachedResponse) {
                return cachedResponse;
              }
              // If no cached data, return offline indicator
              return new Response(JSON.stringify({
                success: false,
                offline: true,
                error: 'No internet connection and no cached data available'
              }), {
                headers: { 'Content-Type': 'application/json' }
              });
            });
        })
    );
    return;
  }
  
  // Handle navigation requests
  if (event.request.mode === 'navigate') {
    event.respondWith(
      fetch(event.request)
        .catch(() => {
          // If offline and trying to access plans, redirect to offline page
          if (url.pathname.includes('/dashboard.php') || 
              url.pathname.includes('/profile.php') ||
              url.pathname.includes('/plans')) {
            return caches.match(OFFLINE_URL);
          }
          
          // For other pages, try cache first
          return caches.match(event.request);
        })
    );
    return;
  }
  
  // Default strategy: cache first, then network
  event.respondWith(
    caches.match(event.request)
      .then(response => {
        if (response) {
          return response;
        }
        
        return fetch(event.request)
          .then(response => {
            // Don't cache non-successful responses
            if (!response || response.status !== 200 || response.type !== 'basic') {
              return response;
            }
            
            // Cache successful responses
            const responseToCache = response.clone();
            caches.open(CACHE_NAME)
              .then(cache => {
                cache.put(event.request, responseToCache);
              });
            
            return response;
          });
      })
  );
});

// Background sync for when connection is restored
self.addEventListener('sync', event => {
  if (event.tag === 'sync-plans-data') {
    event.waitUntil(
      syncPlansData()
    );
  }
});

// Sync function to update cached data when online
async function syncPlansData() {
  try {
    console.log('Syncing plans data...');
    
    const response = await fetch('/Capstone-latest/public/api/offline_plans.php?action=all');
    if (response.ok) {
      const cache = await caches.open(CACHE_NAME);
      await cache.put('/Capstone-latest/public/api/offline_plans.php?action=all', response.clone());
      
      // Notify all clients that data has been updated
      const clients = await self.clients.matchAll();
      clients.forEach(client => {
        client.postMessage({
          type: 'DATA_SYNCED',
          message: 'Plans data updated'
        });
      });
      
      console.log('Plans data synced successfully');
    }
  } catch (error) {
    console.error('Failed to sync plans data:', error);
  }
}

// Handle messages from the main app
self.addEventListener('message', event => {
  if (event.data && event.data.type === 'SKIP_WAITING') {
    self.skipWaiting();
  }
  
  if (event.data && event.data.type === 'CACHE_PLANS_DATA') {
    // Cache plans data when user is online
    const data = event.data.data;
    caches.open(CACHE_NAME).then(cache => {
      const response = new Response(JSON.stringify(data), {
        headers: { 'Content-Type': 'application/json' }
      });
      cache.put('/Capstone-latest/public/api/offline_plans.php?action=all', response);
    });
  }
});