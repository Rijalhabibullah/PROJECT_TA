"""
Database Model untuk menyimpan riwayat klasifikasi
"""

from datetime import datetime
from sqlalchemy import Column, Integer, String, Float, DateTime, JSON
from sqlalchemy.ext.declarative import declarative_base

Base = declarative_base()

class Classification(Base):
    """Model untuk menyimpan hasil klasifikasi"""
    __tablename__ = 'classifications'
    
    id = Column(Integer, primary_key=True)
    image_path = Column(String(255), nullable=False)
    filename = Column(String(255), nullable=False)
    predicted_class = Column(String(100), nullable=False)
    confidence = Column(Float, nullable=False)
    all_predictions = Column(JSON, nullable=False)
    disease_name = Column(String(255), nullable=True)
    severity = Column(String(50), nullable=True)
    notes = Column(String(500), nullable=True)
    created_at = Column(DateTime, default=datetime.utcnow)
    
    def to_dict(self):
        """Convert ke dictionary"""
        return {
            'id': self.id,
            'filename': self.filename,
            'predicted_class': self.predicted_class,
            'confidence': self.confidence,
            'all_predictions': self.all_predictions,
            'disease_name': self.disease_name,
            'severity': self.severity,
            'notes': self.notes,
            'created_at': self.created_at.isoformat()
        }
