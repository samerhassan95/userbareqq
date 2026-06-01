# Knowledge API Contract (Client App Keys)

This document captures the exact keys and response shapes expected by the mobile app Knowledge feature.

## Endpoints used by app

- `GET client/sections`
- `GET client/sections/{sectionId}`
- `GET client/topics/{topicId}`

---

## 1) Sections list

### `GET client/sections`

### Response shape

```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "label": "General",
      "total_count": 12,
      "topics_preview": [
        {
          "id": 10,
          "section_id": 1,
          "section_label": "General",
          "header": "How billing works",
          "description": "Short preview text",
          "date": "2026-06-01",
          "created_at": "2026-06-01 10:00:00"
        }
      ]
    }
  ]
}
```

### Required keys

Top-level:
- `success`
- `data`

For each section in `data[]`:
- `id`
- `label`
- `total_count`
- `topics_preview`

For each item in `topics_preview[]`:
- `id`
- `section_id`
- `section_label`
- `header`
- `description`
- `date`
- `created_at`

---

## 2) Section details

### `GET client/sections/{sectionId}`

### Response shape

```json
{
  "success": true,
  "data": {
    "section_id": 1,
    "section_label": "General",
    "topics": [
      {
        "id": 10,
        "section_id": 1,
        "section_label": "General",
        "header": "How billing works",
        "description": "Full description",
        "date": "2026-06-01",
        "created_at": "2026-06-01 10:00:00",
        "steps": [
          {
            "step_number": 1,
            "title": "Step title",
            "description": "Step description"
          }
        ],
        "feedback": {
          "yes_count": 20,
          "no_count": 3
        },
        "related_articles": [
          {
            "id": 12,
            "header": "Related topic"
          }
        ]
      }
    ]
  }
}
```

### Required keys

Top-level:
- `success`
- `data`

Inside `data`:
- `section_id`
- `section_label`
- `topics`

Each topic in `topics[]`:
- `id`
- `section_id`
- `section_label`
- `header`
- `description`
- `date`
- `created_at`
- `steps`
- `feedback`
- `related_articles`

Each step in `steps[]`:
- `step_number`
- `title`
- `description`

`feedback` object:
- `yes_count`
- `no_count`

Each related article:
- `id`
- `header`

---

## 3) Topic details

### `GET client/topics/{topicId}`

### Response shape

```json
{
  "success": true,
  "data": {
    "id": 10,
    "section_id": 1,
    "section_label": "General",
    "header": "How billing works",
    "description": "Full description",
    "date": "2026-06-01",
    "created_at": "2026-06-01 10:00:00",
    "steps": [
      {
        "step_number": 1,
        "title": "Step title",
        "description": "Step description"
      }
    ],
    "feedback": {
      "yes_count": 20,
      "no_count": 3
    },
    "related_articles": [
      {
        "id": 12,
        "header": "Related topic"
      }
    ]
  }
}
```

### Required keys

Top-level:
- `success`
- `data`

Inside `data`:
- `id`
- `section_id`
- `section_label`
- `header`
- `description`
- `date`
- `created_at`
- `steps`
- `feedback`
- `related_articles`

---

## 4) UI localization keys (not API)

From `lib/features/Knowledge/presentation/knowledge_view.dart`, UI keys include:

- `'knowledge_base'.tr()`
- `'find_answers'.tr()`

These are translation keys only and are not backend payload fields.
