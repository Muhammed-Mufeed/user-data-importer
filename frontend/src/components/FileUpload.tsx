import React, { useRef, useState } from 'react';
import { UploadCloud, FileText, CheckCircle2, AlertCircle, ArrowRight, Loader2 } from 'lucide-react';

interface FileUploadProps {
  onFileSelected: (file: File) => void;
  isLoading: boolean;
  selectedFile: File | null;
  onClear: () => void;
}

export const FileUpload: React.FC<FileUploadProps> = ({
  onFileSelected,
  isLoading,
  selectedFile,
  onClear,
}) => {
  const [isDragOver, setIsDragOver] = useState(false);
  const [errorMessage, setErrorMessage] = useState<string | null>(null);
  const fileInputRef = useRef<HTMLInputElement>(null);

  const handleFile = (file: File) => {
    setErrorMessage(null);
    if (!file.name.toLowerCase().endsWith('.csv')) {
      setErrorMessage('Please select a valid .csv file format.');
      return;
    }
    onFileSelected(file);
  };

  const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragOver(false);
    if (e.dataTransfer.files && e.dataTransfer.files.length > 0) {
      handleFile(e.dataTransfer.files[0]);
    }
  };

  const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
    e.preventDefault();
    setIsDragOver(true);
  };

  const handleDragLeave = () => {
    setIsDragOver(false);
  };

  return (
    <div className="w-full">
      <div
        onDrop={handleDrop}
        onDragOver={handleDragOver}
        onDragLeave={handleDragLeave}
        onClick={() => fileInputRef.current?.click()}
        className={`relative border-2 border-dashed rounded-2xl p-8 transition cursor-pointer text-center flex flex-col items-center justify-center ${
          isDragOver
            ? 'border-indigo-500 bg-indigo-500/10 scale-[1.01]'
            : selectedFile
            ? 'border-emerald-500/50 bg-emerald-500/5'
            : 'border-slate-800 bg-slate-900/40 hover:border-slate-700 hover:bg-slate-900/60'
        }`}
      >
        <input
          type="file"
          ref={fileInputRef}
          onChange={(e) => e.target.files?.[0] && handleFile(e.target.files[0])}
          accept=".csv"
          className="hidden"
        />

        {selectedFile ? (
          <div className="flex flex-col items-center gap-3">
            <div className="w-14 h-14 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
              <CheckCircle2 className="w-7 h-7" />
            </div>
            <div>
              <h3 className="font-semibold text-slate-200 text-sm">{selectedFile.name}</h3>
              <p className="text-xs text-slate-400 mt-0.5">
                {(selectedFile.size / 1024).toFixed(1)} KB • Ready for validation
              </p>
            </div>
            <div className="flex items-center gap-3 mt-2">
              <button
                type="button"
                onClick={(e) => {
                  e.stopPropagation();
                  onClear();
                }}
                className="px-3 py-1 text-xs text-slate-400 hover:text-slate-200 bg-slate-800/80 rounded-lg border border-slate-700 transition"
              >
                Choose another file
              </button>
            </div>
          </div>
        ) : (
          <div className="flex flex-col items-center gap-3">
            <div className="w-14 h-14 rounded-2xl bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400">
              <UploadCloud className="w-7 h-7" />
            </div>
            <div>
              <h3 className="font-semibold text-slate-200 text-sm">
                Drag and drop your CSV file here, or <span className="text-indigo-400 hover:underline">browse</span>
              </h3>
              <p className="text-xs text-slate-400 mt-1">
                Supports standard CSV with <code className="text-slate-300">name, surname, email</code> headers
              </p>
            </div>
          </div>
        )}

        {isLoading && (
          <div className="absolute inset-0 bg-slate-950/80 rounded-2xl backdrop-blur-sm flex flex-col items-center justify-center gap-2">
            <Loader2 className="w-8 h-8 text-indigo-400 animate-spin" />
            <p className="text-xs text-indigo-300 font-medium">Validating and normalizing CSV records...</p>
          </div>
        )}
      </div>

      {errorMessage && (
        <div className="mt-3 flex items-center gap-2 px-3 py-2 bg-rose-500/10 border border-rose-500/20 text-rose-400 text-xs rounded-xl">
          <AlertCircle className="w-4 h-4 shrink-0" />
          <span>{errorMessage}</span>
        </div>
      )}
    </div>
  );
};
