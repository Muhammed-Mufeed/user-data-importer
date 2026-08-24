import React from 'react';
import { Users, CheckCircle, XCircle, Database } from 'lucide-react';
import { ValidationSummary } from '../types';

interface StatsSummaryProps {
  summary: ValidationSummary;
}

export const StatsSummary: React.FC<StatsSummaryProps> = ({ summary }) => {
  const isImported = summary.imported_count !== undefined;
  const allAlreadyExisted = isImported && summary.imported_count === 0 && summary.valid_count > 0;

  const getDbStatusValue = () => {
    if (!isImported) return 'Preview Mode';
    if (allAlreadyExisted) return `${summary.valid_count} in Database`;
    return `${summary.imported_count} Inserted`;
  };

  const getDbStatusSubtext = () => {
    if (!isImported) return 'Simulation (No database writes)';
    if (allAlreadyExisted) return 'All records already exist in database';
    return `${summary.imported_count} new records saved to database`;
  };

  const cards = [
    {
      label: 'Total Rows Parsed',
      value: summary.total_rows,
      subtext: summary.filename || 'Source CSV',
      icon: Users,
      color: 'from-blue-500/20 to-sky-500/5',
      borderColor: 'border-blue-500/30',
      iconColor: 'text-blue-400',
    },
    {
      label: 'Valid Users',
      value: summary.valid_count,
      subtext: `${((summary.valid_count / (summary.total_rows || 1)) * 100).toFixed(0)}% valid rate`,
      icon: CheckCircle,
      color: 'from-emerald-500/20 to-teal-500/5',
      borderColor: 'border-emerald-500/30',
      iconColor: 'text-emerald-400',
    },
    {
      label: 'Invalid / Skipped',
      value: summary.invalid_count,
      subtext: summary.invalid_count > 0 ? `${summary.invalid_count} malformed or duplicate` : 'Zero errors detected',
      icon: XCircle,
      color: 'from-rose-500/20 to-pink-500/5',
      borderColor: 'border-rose-500/30',
      iconColor: 'text-rose-400',
    },
    {
      label: 'Database Status',
      value: getDbStatusValue(),
      subtext: getDbStatusSubtext(),
      icon: Database,
      color: allAlreadyExisted
        ? 'from-blue-500/20 to-indigo-500/5'
        : 'from-purple-500/20 to-indigo-500/5',
      borderColor: allAlreadyExisted ? 'border-blue-500/30' : 'border-purple-500/30',
      iconColor: allAlreadyExisted ? 'text-blue-400' : 'text-purple-400',
    },
  ];

  return (
    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      {cards.map((card, idx) => {
        const Icon = card.icon;
        return (
          <div
            key={idx}
            className={`p-5 rounded-2xl bg-gradient-to-br ${card.color} border ${card.borderColor} backdrop-blur-sm relative overflow-hidden flex flex-col justify-between`}
          >
            <div className="flex items-center justify-between">
              <span className="text-xs font-semibold text-slate-400 uppercase tracking-wider">{card.label}</span>
              <div className={`p-2 rounded-xl bg-slate-900/60 border border-white/5 ${card.iconColor}`}>
                <Icon className="w-5 h-5" />
              </div>
            </div>

            <div className="mt-4">
              <div className="text-2xl font-bold text-white tracking-tight">{card.value}</div>
              <p className="text-xs text-slate-400 mt-1">{card.subtext}</p>
            </div>
          </div>
        );
      })}
    </div>
  );
};
