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

    def upload_image(self, image_path: str, folder: str = "media") -> str:
        with open(image_path, "rb") as f:
            resp = requests.post(
                f"{self.base_url}/api/v1/media/upload",
                headers=self._headers(),
                files={"file": f},
                data={"folder": folder},
            )
        resp.raise_for_status()
        return resp.json()["data"]["url"]

    def create_category(self, name: str, slug: str = "") -> dict:
        data = {"name": name}
        if slug:
            data["slug"] = slug
        resp = requests.post(
            f"{self.base_url}/api/v1/categories",
            json=data,
            headers=self._headers(),
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def create_tag(self, name: str, slug: str = "") -> dict:
        data = {"name": name}
        if slug:
            data["slug"] = slug
        resp = requests.post(
            f"{self.base_url}/api/v1/tags",
            json=data,
            headers=self._headers(),
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def create_post(
        self,
        title: str,
        content: str,
        excerpt: str = "",
        category_id: int = None,
        featured_image: str = "",
        status: str = "published",
        tags: list[str] = None,
        published_at: str = "",
    ) -> dict:
        data = {
            "title": title,
            "content": content,
            "excerpt": excerpt,
            "status": status,
        }
        if category_id:
            data["category_id"] = category_id
        if featured_image:
            data["featured_image"] = featured_image
        if tags:
            data["tags"] = tags
        if published_at:
            data["published_at"] = published_at
        resp = requests.post(
            f"{self.base_url}/api/v1/posts",
            json=data,
            headers=self._headers(),
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def get_categories(self) -> list[dict]:
        resp = requests.get(
            f"{self.base_url}/api/v1/categories",
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def get_tags(self) -> list[dict]:
        resp = requests.get(
            f"{self.base_url}/api/v1/tags",
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def get_site_settings(self) -> dict:
        resp = requests.get(
            f"{self.base_url}/api/v1/settings",
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def get_domains(self) -> list[dict]:
        resp = requests.get(
            f"{self.base_url}/api/v1/domains",
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def get_themes(self) -> list[dict]:
        resp = requests.get(
            f"{self.base_url}/api/v1/themes",
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def create_domains(self, domains: list[str]) -> list[dict]:
        resp = requests.post(
            f"{self.base_url}/api/v1/domains",
            json={"domains": domains},
            headers=self._headers(),
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def update_settings(self, domain_id: int, settings: dict) -> dict:
        resp = requests.put(
            f"{self.base_url}/api/v1/settings",
            json={"domain_id": domain_id, "settings": settings},
            headers=self._headers(),
        )
        resp.raise_for_status()
        return resp.json()["data"]

    def get_last_published_date(self) -> str:
        resp = requests.get(
            f"{self.base_url}/api/v1/posts?per_page=1",
            headers={"Accept": "application/json"},
        )
        resp.raise_for_status()
        data = resp.json().get("data", [])
        if data:
            return data[0].get("published_at", "")
        return ""
