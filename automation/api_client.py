import time

import requests


class BlogCMSClient:
    def __init__(self, base_url: str, email: str = "", password: str = "", token: str = "", api_key: str = ""):
        self.base_url = base_url.rstrip("/")
        self.api_key = api_key
        self.token = token
        if not self.token and email and password:
            self.token = self._login(email, password)

    def _login(self, email: str, password: str) -> str:
        resp = requests.post(
            f"{self.base_url}/api/v1/auth/login",
            json={"email": email, "password": password},
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        return resp.json()["data"]["token"]

    def _headers(self) -> dict:
        headers = {"Accept": "application/json"}
        if self.token:
            headers["Authorization"] = f"Bearer {self.token}"
        if self.api_key:
            headers["X-API-Key"] = self.api_key
        return headers

    def _request(self, method: str, path: str, **kwargs) -> requests.Response:
        url = f"{self.base_url}{path}"
        headers = self._headers()
        if "headers" in kwargs:
            headers.update(kwargs.pop("headers"))

        for attempt in range(3):
            try:
                resp = requests.request(method, url, headers=headers, timeout=120, **kwargs)
                if resp.ok:
                    return resp
                if 500 <= resp.status_code < 600:
                    if attempt < 2:
                        wait = (attempt + 1) * 10
                        print(f"   ⚠️ {method.upper()} {path}: {resp.status_code}, retry {wait}s...")
                        time.sleep(wait)
                        continue
                resp.raise_for_status()
            except (requests.ConnectionError, requests.Timeout) as e:
                if attempt == 2:
                    raise
                wait = (attempt + 1) * 10
                print(f"   ⚠️ {method.upper()} {path}: {e}, retry {wait}s...")
                time.sleep(wait)
        return resp

    def upload_image(self, image_path: str, folder: str = "media") -> str:
        with open(image_path, "rb") as f:
            resp = self._request("POST", "/api/v1/media/upload", files={"file": f}, data={"folder": folder})
        return resp.json()["data"]["url"]

    def create_category(self, name: str, slug: str = "") -> dict:
        data = {"name": name}
        if slug:
            data["slug"] = slug
        resp = self._request("POST", "/api/v1/categories", json=data)
        return resp.json()["data"]

    def create_tag(self, name: str, slug: str = "") -> dict:
        data = {"name": name}
        if slug:
            data["slug"] = slug
        resp = self._request("POST", "/api/v1/tags", json=data)
        return resp.json()["data"]

    def create_post(self, title: str, content: str, excerpt: str = "", category_id: int = None,
                    featured_image: str = "", status: str = "published", tags: list[str] = None,
                    published_at: str = "", seo_title: str = "", seo_description: str = "",
                    seo_keywords: str = "") -> dict:
        data = {"title": title, "content": content, "excerpt": excerpt, "status": status}
        if category_id:
            data["category_id"] = category_id
        if featured_image:
            data["featured_image"] = featured_image
        if tags:
            data["tags"] = tags
        if published_at:
            data["published_at"] = published_at
        if seo_title:
            data["seo_title"] = seo_title
        if seo_description:
            data["seo_description"] = seo_description
        if seo_keywords:
            data["seo_keywords"] = seo_keywords
        resp = self._request("POST", "/api/v1/posts", json=data)
        return resp.json()["data"]

    def update_post(self, post_id: int, **kwargs) -> dict:
        resp = self._request("PUT", f"/api/v1/posts/{post_id}", json=kwargs)
        return resp.json()["data"]

    def get_categories(self) -> list[dict]:
        resp = self._request("GET", "/api/v1/categories")
        return resp.json()["data"]

    def get_tags(self) -> list[dict]:
        resp = self._request("GET", "/api/v1/tags")
        return resp.json()["data"]

    def get_site_settings(self) -> dict:
        resp = self._request("GET", "/api/v1/settings")
        return resp.json()["data"]

    def get_domains(self) -> list[dict]:
        resp = self._request("GET", "/api/v1/domains")
        return resp.json()["data"]

    def get_themes(self) -> list[dict]:
        resp = self._request("GET", "/api/v1/themes")
        return resp.json()["data"]

    def create_domains(self, domains: list[str]) -> list[dict]:
        resp = self._request("POST", "/api/v1/domains", json={"domains": domains})
        return resp.json()["data"]

    def update_settings(self, domain_id: int, settings: dict) -> dict:
        resp = self._request("PUT", "/api/v1/settings", json={"domain_id": domain_id, "settings": settings})
        return resp.json()["data"]

    def get_last_published_date(self) -> str:
        resp = self._request("GET", "/api/v1/posts?per_page=1")
        data = resp.json().get("data", [])
        if data:
            return data[0].get("published_at", "")
        return ""
