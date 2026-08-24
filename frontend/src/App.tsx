import React, { useState } from 'react';
import { Header } from './components/Header';
import { FileUpload } from './components/FileUpload';
import { StatsSummary } from './components/StatsSummary';
import { PreviewTable } from './components/PreviewTable';
import { ImportAction } from './components/ImportAction';
import { DatabaseViewerModal } from './components/DatabaseViewerModal';
import { validateCsv, importUsers } from './services/api';
import { ValidationSummary } from './types';
import { Database, FileSpreadsheet, Sparkles, RefreshCw, AlertCircle } from 'lucide-react';

export const App: React.FC = () => {
  const [selectedFile, setSelectedFile] = useState<File | null>(null);
  const [validationResult, setValidationResult] = useState<ValidationSummary | null>(null);
  const [isValidating, setIsValidating] = useState(false);
  const [isImporting, setIsImporting] = useState(false);
  const [importSummary, setImportSummary] = useState<ValidationSummary | null>(null);
  const [errorMsg, setErrorMsg] = useState<string | null>(null);
  const [isDbModalOpen, setIsDbModalOpen] = useState(false);

  const handleFileSelected = async (file: File) => {
    setSelectedFile(file);
    setValidationResult(null);
    setImportSummary(null);
    setErrorMsg(null);
    setIsValidating(true);

    try {
      const result = await validateCsv(file);
      setValidationResult(result);
    } catch (err: any) {
      setErrorMsg(err.message || 'Validation failed.');
    } finally {
      setIsValidating(false);
    }
  };

  const handleImport = async () => {
    if (!selectedFile) return;
    setIsImporting(true);
    setErrorMsg(null);

    try {
      const result = await importUsers(selectedFile);
      setImportSummary(result);
    } catch (err: any) {
      setErrorMsg(err.message || 'Import failed.');
    } finally {
      setIsImporting(false);
    }
  };

  const handleClear = () => {
    setSelectedFile(null);
    setValidationResult(null);
    setImportSummary(null);
    setErrorMsg(null);
  };

  // Sample CSV loader shortcut
  const handleLoadSample = async () => {
    try {
      const res = await fetch('/data/users.csv');
      if (!res.ok) {
        // Fallback: create sample blob
        const sampleCsv = `name,surname,email\njohn,smith,john.smith@example.com\nJANE,DOE,jane.doe@example.com\nmissing,domain,missing@\ninvalid,email,invalid-email\n`;
        const file = new File([sampleCsv], 'sample_users.csv', { type: 'text/csv' });
        handleFileSelected(file);
        return;
      }
      const blob = await res.blob();
      const file = new File([blob], 'users.csv', { type: 'text/csv' });
      handleFileSelected(file);
    } catch (e) {
      setErrorMsg('Could not load sample file.');
    }
  };

  const activeSummary = importSummary || validationResult;

  return (
    <div className="min-h-screen bg-slate-950 text-slate-100 flex flex-col selection:bg-indigo-500 selection:text-white">
      {/* Header */}
      <Header />

      {/* Main Container */}
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col gap-8">
        {/* Hero / Header Section */}
        <div className="flex flex-col md:flex-row md:items-end justify-between gap-4 pb-2 border-b border-slate-800/80">
          <div>
            <span className="text-xs font-bold uppercase tracking-wider text-indigo-400">
              CSV Normalization & Import Pipeline
            </span>
            <h2 className="text-2xl sm:text-3xl font-extrabold text-white tracking-tight mt-1">
              User Data Importer Dashboard
            </h2>
            <p className="text-sm text-slate-400 mt-1 max-w-2xl">
              Upload CSV files to automatically normalize names, validate RFC email standards, filter duplicate entries, and securely import valid user records.
            </p>
          </div>

          <div className="flex items-center gap-3">
            <button
              onClick={() => setIsDbModalOpen(true)}
              className="flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-900 hover:bg-slate-800 text-slate-200 text-xs font-semibold border border-slate-800 transition"
            >
              <Database className="w-4 h-4 text-indigo-400" />
              <span>View Database Records</span>
            </button>
          </div>
        </div>

        {/* Global Error Banner */}
        {errorMsg && (
          <div className="p-4 rounded-xl bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs flex items-center justify-between">
            <div className="flex items-center gap-2">
              <AlertCircle className="w-4 h-4 shrink-0" />
              <span>{errorMsg}</span>
            </div>
            <button
              onClick={() => setErrorMsg(null)}
              className="text-rose-400 hover:text-rose-200 text-xs underline"
            >
              Dismiss
            </button>
          </div>
        )}

        {/* Upload Zone */}
        <section className="flex flex-col gap-3">
          <div className="flex items-center justify-between">
            <h3 className="text-sm font-bold text-slate-200 flex items-center gap-2">
              <FileSpreadsheet className="w-4 h-4 text-indigo-400" />
              <span>1. Upload CSV File</span>
            </h3>

            {!selectedFile && (
              <div className="flex items-center gap-2 text-xs">
                <span className="text-slate-500 hidden sm:inline">Don't have a CSV file ready?</span>
                <button
                  onClick={handleLoadSample}
                  className="px-2.5 py-1 rounded-lg bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 border border-indigo-500/20 transition flex items-center gap-1.5 font-medium"
                  title="Load sample dataset (users.csv) to test immediately"
                >
                  <Sparkles className="w-3.5 h-3.5" />
                  <span>Try Sample Data</span>
                </button>
              </div>
            )}
          </div>

          <FileUpload
            onFileSelected={handleFileSelected}
            isLoading={isValidating}
            selectedFile={selectedFile}
            onClear={handleClear}
          />
        </section>

        {/* Results & Actions Section */}
        {activeSummary && (
          <section className="flex flex-col gap-6 animate-fadeIn">
            {/* Step 2: Metrics Summary */}
            <div className="flex flex-col gap-3">
              <h3 className="text-sm font-bold text-slate-200">
                2. Validation Metrics & Status
              </h3>
              <StatsSummary summary={activeSummary} />
            </div>

            {/* Step 3: Import Action Bar */}
            <ImportAction
              validCount={activeSummary.valid_count}
              invalidCount={activeSummary.invalid_count}
              onImport={handleImport}
              isLoading={isImporting}
              importedCount={importSummary?.imported_count}
            />

            {/* Step 4: Preview Table */}
            <div className="flex flex-col gap-3">
              <h3 className="text-sm font-bold text-slate-200">
                3. Row-by-Row Record Preview
              </h3>
              <PreviewTable records={activeSummary.records} />
            </div>
          </section>
        )}
      </main>

      {/* Database Viewer Modal */}
      <DatabaseViewerModal
        isOpen={isDbModalOpen}
        onClose={() => setIsDbModalOpen(false)}
      />

      {/* Footer */}
      <footer className="border-t border-slate-900 bg-slate-950 py-6 text-center text-xs text-slate-500">
        <p>© {new Date().getFullYear()} User Data Importer • Secure Batch Data Processing Pipeline</p>
      </footer>
    </div>
  );
};

export default App;
