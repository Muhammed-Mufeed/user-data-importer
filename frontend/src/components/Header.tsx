import React, { useState } from 'react';
import { Database, RefreshCw, CheckCircle2, AlertCircle } from 'lucide-react';
import { createDatabaseTable } from '../services/api';

interface HeaderProps {
  onTableCreated?: () => void;
}

export const Header: React.FC<HeaderProps> = ({ onTableCreated }) => {
  const [loading, setLoading] = useState(false);
  const [statusMsg, setStatusMsg] = useState<{ text: string; isError: boolean } | null>(null);

  const handleCreateTable = async () => {
    setLoading(true);
    setStatusMsg(null);
    try {
      const res = await createDatabaseTable();
      setStatusMsg({ text: res.message || 'PostgreSQL table initialized!', isError: false });
      if (onTableCreated) onTableCreated();
    } catch (err: any) {
      setStatusMsg({ text: err.message || 'Failed to connect to database', isError: true });
    } finally {
      setLoading(false);
      setTimeout(() => setStatusMsg(null), 4000);
    }
  };

  return (
    <header className="border-b border-slate-800/80 bg-slate-900/60 backdrop-blur-md sticky top-0 z-50">
      <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row items-center justify-between gap-4">
        {/* Brand */}
        <div className="flex items-center gap-3">
          <div className="w-10 h-10 rounded-xl bg-gradient-to-tr from-indigo-600 to-violet-500 flex items-center justify-center shadow-lg shadow-indigo-500/25">
            <Database className="w-5 h-5 text-white" />
          </div>
          <div>
            <div className="flex items-center gap-2">
              <h1 className="text-lg font-bold text-white tracking-tight">User Data Importer</h1>
            </div>
            <p className="text-xs text-slate-400">Stream CSV Parsing, Data Normalization & Batch Persistence</p>
          </div>
        </div>

        {/* Database Action */}
        <div className="flex items-center gap-3">
          {statusMsg && (
            <div
              className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium ${
                statusMsg.isError
                  ? 'bg-rose-500/10 text-rose-400 border border-rose-500/20'
                  : 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20'
              }`}
            >
              {statusMsg.isError ? <AlertCircle className="w-3.5 h-3.5" /> : <CheckCircle2 className="w-3.5 h-3.5" />}
              {statusMsg.text}
            </div>
          )}

          <button
            onClick={handleCreateTable}
            disabled={loading}
            className="flex items-center gap-2 px-3.5 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-medium border border-slate-700 transition disabled:opacity-50"
            title="Create/Verify users table in PostgreSQL"
          >
            <RefreshCw className={`w-3.5 h-3.5 ${loading ? 'animate-spin' : ''}`} />
            {loading ? 'Verifying Database...' : 'Verify Database Table'}
          </button>
        </div>
      </div>
    </header>
  );
};
