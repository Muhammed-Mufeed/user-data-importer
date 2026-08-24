import React, { useState, useMemo } from 'react';
import { Search, CheckCircle2, AlertTriangle, Filter, ArrowUpDown } from 'lucide-react';
import { UserRecord } from '../types';

interface PreviewTableProps {
  records: UserRecord[];
}

export const PreviewTable: React.FC<PreviewTableProps> = ({ records }) => {
  const [filter, setFilter] = useState<'ALL' | 'VALID' | 'ERROR'>('ALL');
  const [search, setSearch] = useState('');
  const [sortAsc, setSortAsc] = useState(true);

  const filteredRecords = useMemo(() => {
    return records
      .filter((record) => {
        // Tab Filter
        if (filter === 'VALID' && record.status !== 'VALID') return false;
        if (filter === 'ERROR' && record.status !== 'ERROR') return false;

        // Search Filter
        if (!search.trim()) return true;
        const q = search.toLowerCase();
        return (
          record.name.toLowerCase().includes(q) ||
          record.surname.toLowerCase().includes(q) ||
          record.email.toLowerCase().includes(q) ||
          String(record.row).includes(q) ||
          (record.error && record.error.toLowerCase().includes(q))
        );
      })
      .sort((a, b) => (sortAsc ? a.row - b.row : b.row - a.row));
  }, [records, filter, search, sortAsc]);

  const validCount = records.filter((r) => r.status === 'VALID').length;
  const errorCount = records.filter((r) => r.status === 'ERROR').length;

  return (
    <div className="w-full rounded-2xl bg-slate-900/60 border border-slate-800/80 overflow-hidden flex flex-col">
      {/* Controls Bar */}
      <div className="p-4 sm:p-5 border-b border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4">
        {/* Filter Tabs */}
        <div className="flex items-center gap-1.5 p-1 bg-slate-950/60 border border-slate-800 rounded-xl w-full sm:w-auto">
          <button
            onClick={() => setFilter('ALL')}
            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 ${filter === 'ALL'
              ? 'bg-indigo-600 text-white shadow-sm'
              : 'text-slate-400 hover:text-slate-200'
              }`}
          >
            All <span className="px-1.5 py-0.2 bg-white/20 rounded-md text-[10px]">{records.length}</span>
          </button>
          <button
            onClick={() => setFilter('VALID')}
            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 ${filter === 'VALID'
              ? 'bg-emerald-600 text-white shadow-sm'
              : 'text-slate-400 hover:text-emerald-400'
              }`}
          >
            Valid <span className="px-1.5 py-0.2 bg-white/20 rounded-md text-[10px]">{validCount}</span>
          </button>
          <button
            onClick={() => setFilter('ERROR')}
            className={`px-3.5 py-1.5 rounded-lg text-xs font-semibold transition flex items-center gap-1.5 ${filter === 'ERROR'
              ? 'bg-rose-600 text-white shadow-sm'
              : 'text-slate-400 hover:text-rose-400'
              }`}
          >
            Errors <span className="px-1.5 py-0.2 bg-white/20 rounded-md text-[10px]">{errorCount}</span>
          </button>
        </div>

        {/* Search & Sort */}
        <div className="flex items-center gap-3 w-full sm:w-auto">
          <div className="relative flex-1 sm:w-64">
            <Search className="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input
              type="text"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              placeholder="Search by name, email, row..."
              className="w-full pl-9 pr-4 py-1.5 bg-slate-950/60 border border-slate-800 rounded-xl text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-indigo-500 transition"
            />
          </div>

          <button
            onClick={() => setSortAsc(!sortAsc)}
            className="p-2 rounded-xl bg-slate-950/60 border border-slate-800 text-slate-400 hover:text-slate-200 transition"
            title={`Sort by Row #${sortAsc ? ' (Ascending)' : ' (Descending)'}`}
          >
            <ArrowUpDown className="w-4 h-4" />
          </button>
        </div>
      </div>

      {/* Table Container */}
      <div className="overflow-x-auto max-h-[480px]">
        <table className="w-full text-left text-xs">
          <thead className="sticky top-0 bg-slate-900 border-b border-slate-800 text-slate-400 uppercase tracking-wider font-semibold">
            <tr>
              <th className="py-3 px-4 w-16">Row</th>
              <th className="py-3 px-4 w-28">Status</th>
              <th className="py-3 px-4">First Name</th>
              <th className="py-3 px-4">Last Name</th>
              <th className="py-3 px-4">Email Address</th>
              <th className="py-3 px-4">Validation Remark</th>
            </tr>
          </thead>
          <tbody className="divide-y divide-slate-800/60">
            {filteredRecords.length === 0 ? (
              <tr>
                <td colSpan={6} className="py-8 text-center text-slate-500">
                  No records match the current filter.
                </td>
              </tr>
            ) : (
              filteredRecords.map((record, index) => {
                const isValid = record.status === 'VALID';
                return (
                  <tr
                    key={index}
                    className={`transition hover:bg-slate-800/40 ${!isValid ? 'bg-rose-500/[0.03]' : ''
                      }`}
                  >
                    <td className="py-3 px-4 text-slate-400 font-mono">#{record.row}</td>
                    <td className="py-3 px-4">
                      {isValid ? (
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                          <CheckCircle2 className="w-3 h-3" /> Valid
                        </span>
                      ) : (
                        <span className="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-rose-500/10 text-rose-400 border border-rose-500/20">
                          <AlertTriangle className="w-3 h-3" /> Invalid
                        </span>
                      )}
                    </td>
                    <td className="py-3 px-4 font-medium text-slate-200">
                      {record.name || <span className="text-slate-600 italic">missing</span>}
                    </td>
                    <td className="py-3 px-4 font-medium text-slate-200">
                      {record.surname || <span className="text-slate-600 italic">missing</span>}
                    </td>
                    <td className="py-3 px-4 font-mono text-slate-300">
                      {record.email || <span className="text-slate-600 italic">missing</span>}
                    </td>
                    <td className="py-3 px-4">
                      {isValid ? (
                        <span className="text-emerald-400/90 text-xs">Normalized and ready for database</span>
                      ) : (
                        <span className="text-rose-400 font-medium text-xs flex items-center gap-1">
                          {record.error}
                        </span>
                      )}
                    </td>
                  </tr>
                );
              })
            )}
          </tbody>
        </table>
      </div>

      {/* Footer / Count */}
      <div className="py-3 px-5 border-t border-slate-800/80 bg-slate-950/40 text-xs text-slate-400 flex items-center justify-between">
        <span>Showing {filteredRecords.length} of {records.length} records</span>
        <span className="text-slate-500">PostgreSQL Users Table Schema</span>
      </div>
    </div>
  );
};
