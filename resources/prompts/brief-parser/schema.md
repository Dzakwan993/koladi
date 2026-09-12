{  
  "summary": {  
    "project_name": "Project name or null if cannot be reasonably inferred (string/null)",  
    "project_description": "Concise summary (1-3 sentences) of the project (string)",  
    "deliverables": ["Array of extracted deliverables, e.g., 'Landing Page', 'Logo', or empty array if none (array of strings)"],  
    "main_deadline": "Project main deadline, keeping relative dates exactly as written, or null if not found (string/null)"  
  },  
  "decisions": [
    {
      "title": "Decision title (string)",
      "sources": ["Array of source document names supporting this decision, e.g. 'Client Brief.pdf' (array of strings)"]
    }
  ],
  "tasks": [  
    {  
      "title": "Task title (string)",  
      "description": "Short task description (string)",  
      "priority": "low, medium, high, urgent, or null if no urgency evidence exists (string/null)",  
      "start_date": "Task start date in YYYY-MM-DD format (e.g. '2026-09-15'), or null if not found. Convert relative or human-readable dates to ISO format. (string/null)",
      "deadline": "Task deadline in YYYY-MM-DD format (e.g. '2026-09-30'), or null if not found. Convert relative or human-readable dates to ISO format. (string/null)",
      "phase": "Phase/milestone name if explicitly stated or inferred from document structure (e.g. 'Fase 1: Desain', 'PHASE 01 - Manufacturing'), 'Klarifikasi' for clarification/missing info tasks, or null (string/null)",
      "action": "Either 'create' for new tasks or 'update' for existing tasks being modified (string)",
      "existing_task_id": "ID of the matching existing task if action is 'update', or null if action is 'create' (string/null)",
      "reason": "Short explanation in Bahasa Indonesia of what changed for updated tasks, or null (string/null)"
    }  
  ],  
  "missing_information": ["Array of genuinely missing and important information categories/items, or empty array (array of strings)"],  
  "clarification_questions": ["Array of specific questions generated from missing information, or empty array (array of strings)"]  
}
