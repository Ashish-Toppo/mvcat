const CACHE_NAME = "events-cache-v2";

self.addEventListener("install", (event) => {
  event.waitUntil(
    caches.open(CACHE_NAME).then((cache) =>
      cache.addAll(["/event/"])
    )
  );
});

self.addEventListener("fetch", (event) => {
  const request = event.request;

  // Skip chrome extensions and non-GET requests
  if (
    request.url.startsWith("chrome-extension://") ||
    request.url.includes("extension") ||
    request.method !== "GET"
  ) {
    return;
  }

  event.respondWith(
    fetch(request)
      .then((networkResponse) => {
        if (
          networkResponse &&
          networkResponse.status === 200 &&
          (request.url.startsWith("http://") || request.url.startsWith("https://"))
        ) {
          const responseClone = networkResponse.clone();
          caches.open(CACHE_NAME).then((cache) => {
            cache.put(request, responseClone);
          });
        }
        return networkResponse;
      })
      .catch(() => caches.match(request))
  );
});
