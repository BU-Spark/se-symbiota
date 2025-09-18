# Connections between the Repositories

## se-symbiota - Entry Point
This repository acts as the entry point for the OCR feature. 
### Frontend Entry Points:
- **Occurrence Editor:** `collections/editor/occurrenceeditor.php` and `collections/quickentry/occurrencequickentry.php`
- **JavaScript Interface:** `js/symb/collections.editor.imgtools.js` provides the user interface for OCR functionality
- **Image Processing:** Users select the OCR methods through a dropdown menu in the specimen image processing interface
### Key Files:
- `collections/quickentry/rpc/externalocr.php` - connection point that routes requests to the middleware
- `collections/editor/rpc/ocrimage.php` - handles internal Tesseract OCR processing
- `classes/SpecProcessorOcr.php` - main OCR processing class with both local Tesseract and external service support

## herbaria-ocr-middleware - Routing Layer
This repository acts as a middleware service.
### Routing Function:
- Receives requests from `se-symbiota` at `http://ocr_middleware:8000/evaluate/azure`
- Routes requests to `spark-symbiota-ml` at `http://ocr_service:8000/azure` 
- Returns processed results back to `se-symbiota`
### Key Files:
- `main.py` - FastAPI server with routing logic
- `/evaluate/azure` endpoint - forwards image URLs to the ML service
- `evaluate/mock/{id}` endpoint - for testing with mock data
### Configurations:
- Runs on port `8000` (`config/config.yaml`)
- Deployed as `ocr_middleware` service in Docker

## spark-symbiota-ml - ML Processing Backend
This repository is where the actual OCR processing happens.
### Processing Pipeline:
- `backend/main.py` - FastAPI server that receives the image URLs
- `transcriptions/doc_intelligence.py` - Azure Document Intelligence integration
- `/azure` endpoint - downloads images, processes them through Azure OCR, returns structured results
### OCR Technologies:
- Azure Document Intelligence (primary)
- Tesseract OCR (fallback/alternative)
- Various ML models for text extraction and parsing
### Configurations:
- Runs on port `8080`
- Deployed as `ocr_service` service in Docker
