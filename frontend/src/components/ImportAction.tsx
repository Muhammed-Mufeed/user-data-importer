import React from 'react';
import { Database, ArrowRight, Loader2, CheckCircle2, ShieldCheck, Info } from 'lucide-react';

interface ImportActionProps {
  validCount: number;
  invalidCount: number;
  onImport: () => void;
  isLoading: boolean;
  importedCount?: number;
}

export const ImportAction: React.FC<ImportActionProps> = ({
  validCount,
  invalidCount,
  onImport,
  isLoading,
  importedCount,
}) => {
  const isCompleted = importedCount !== undefined;
  const allAlreadyExisted = isCompleted && importedCount === 0 && validCount > 0;
  const partialAlreadyExisted = isCompleted && importedCount > 0 && importedCount < validCount;

  return (
    <div className="w-full rounded-2xl bg-gradient-to-r from-indigo-950/40 via-slate-900 to-purple-950/40 border border-indigo-500/20 p-6 flex flex-col sm:flex-row items-center justify-between gap-6 shadow-xl">
      <div className="flex items-start gap-4">
        <div
          className={`p-3 rounded-2xl border shrink-0 ${
            allAlreadyExisted
              ? 'bg-blue-500/10 border-blue-500/30 text-blue-400'
              : 'bg-indigo-500/10 border-indigo-500/30 text-indigo-400'
          }`}
        >
          {allAlreadyExisted ? <Info className="w-6 h-6" /> : <ShieldCheck className="w-6 h-6" />}
        </div>
        <div>
          <h3 className="text-base font-bold text-white tracking-tight">
            {!isCompleted
              ? 'Ready to Commit Valid Records'
              : allAlreadyExisted
              ? 'All Valid Records Already in Database'
              : partialAlreadyExisted
              ? 'Import Completed with Existing Records Handled'
              : 'Import Completed Successfully!'}
          </h3>
          <p className="text-xs text-slate-300 mt-1 max-w-xl">
            {!isCompleted ? (
              <span>
                {validCount} records passed RFC email validation and capitalization normalization.{' '}
                {invalidCount > 0 && `${invalidCount} invalid rows will be skipped.`}
              </span>
            ) : allAlreadyExisted ? (
              <span className="text-blue-300 font-medium">
                All {validCount} valid users are already present in PostgreSQL. Duplicate insertion was safely prevented.
              </span>
            ) : partialAlreadyExisted ? (
              <span className="text-emerald-300 font-medium">
                Inserted {importedCount} new users. {validCount - importedCount} users already existed in database and were skipped.
              </span>
            ) : (
              <span className="text-emerald-400 font-medium">
                Successfully inserted {importedCount} new users into PostgreSQL. All {invalidCount} invalid rows were safely skipped.
              </span>
            )}
          </p>
        </div>
      </div>

      <div className="flex items-center gap-3 shrink-0">
        {isCompleted ? (
          <div
            className={`flex items-center gap-2 px-5 py-2.5 rounded-xl text-xs font-bold border ${
              allAlreadyExisted
                ? 'bg-blue-500/10 border-blue-500/30 text-blue-400'
                : 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400'
            }`}
          >
            <CheckCircle2 className="w-4 h-4" />
            {allAlreadyExisted
              ? `${validCount} Users Up to Date in Database`
              : `${importedCount} New Users Saved in Database`}
          </div>
        ) : (
          <button
            onClick={onImport}
            disabled={isLoading || validCount === 0}
            className="flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-indigo-600 to-violet-600 hover:from-indigo-500 hover:to-violet-500 text-white font-semibold text-xs tracking-wide shadow-lg shadow-indigo-500/25 transition transform active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed"
          >
            {isLoading ? (
              <>
                <Loader2 className="w-4 h-4 animate-spin" />
                <span>Importing to Database...</span>
              </>
            ) : (
              <>
                <Database className="w-4 h-4" />
                <span>Import {validCount} Valid Users</span>
                <ArrowRight className="w-4 h-4" />
              </>
            )}
          </button>
        )}
      </div>
    </div>
  );
};
