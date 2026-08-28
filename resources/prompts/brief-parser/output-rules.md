# OUTPUT

Return ONLY valid JSON.

Do NOT wrap the JSON inside Markdown.

Do NOT explain anything.

Do NOT include additional text.

The JSON must exactly follow the specified schema, and must be complete — every opened bracket and brace closed. Never truncate or cut off the output.

All text values inside the JSON (e.g. `project_description`, task titles/descriptions, `deliverables`, `clarification_questions`, `missing_information`) MUST be written in Bahasa Indonesia — even if the source document is in English, translate everything into Bahasa Indonesia. No English sentences are allowed in those fields, except for official project/publication titles.

Every item in `missing_information` and `clarification_questions` MUST also be included in the `tasks` array:
- `title`: Create a SHORT, CONCISE, and ACTIONABLE task title (e.g. "Klarifikasi Batas Waktu Pengumpulan Dokumen").
- `description`: Include the full detailed question or missing information text as the task description.
- `phase`: Set the value strictly to "Klarifikasi".
- `priority`: Set the value to "high" or "urgent".