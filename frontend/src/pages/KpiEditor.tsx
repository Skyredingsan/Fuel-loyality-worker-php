import { useEffect, useState } from 'react';
import { kpiService } from '../services/kpi';
import type { KpiIndicator, KpiCategory, IndicatorType } from '../types';

export const KpiEditor: React.FC = () => {
  const [indicators, setIndicators] = useState<KpiIndicator[]>([]);
  const [categories, setCategories] = useState<KpiCategory[]>([]);
  const [loading, setLoading] = useState(true);
  const [showForm, setShowForm] = useState(false);
  const [editingIndicator, setEditingIndicator] = useState<KpiIndicator | null>(null);

  const load = () => {
    setLoading(true);
    Promise.all([kpiService.getIndicators(), kpiService.getCategories()])
      .then(([inds, cats]) => {
        setIndicators(inds);
        setCategories(cats);
      })
      .catch(console.error)
      .finally(() => setLoading(false));
  };

  useEffect(() => {
    load();
  }, []);

  const handleDelete = async (id: number) => {
    if (!confirm('Удалить показатель?')) return;
    try {
      await kpiService.delete(id);
      load();
    } catch (err: any) {
      alert(err.response?.data?.message || 'Ошибка');
    }
  };

  const grouped = (categories || []).map((cat) => ({
    category: cat,
    indicators: (indicators || []).filter((i) => i.category_code === cat.code),
  }));

  return (
    <div className="space-y-6">
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-bold text-gray-900">KPI показатели</h1>
        <button
          onClick={() => {
            setEditingIndicator(null);
            setShowForm(true);
          }}
          className="px-4 py-2 bg-orange-600 text-white rounded hover:bg-orange-700"
        >
          + Добавить показатель
        </button>
      </div>

      {loading && <div className="text-center py-12 text-gray-500">Загрузка...</div>}

      {!loading &&
        grouped.map(({ category, indicators: inds }) => (
          <div key={category.code} className="bg-white rounded-lg shadow">
            <div className="px-6 py-4 border-b">
              <h2 className="text-lg font-semibold">
                {category.code} — {category.name}
              </h2>
              <p className="text-xs text-gray-500 mt-1">{category.description}</p>
            </div>
            <div className="overflow-x-auto">
              <table className="w-full">
                <thead className="bg-gray-50">
                  <tr>
                    <th className="px-4 py-2 text-left text-xs">Код</th>
                    <th className="px-4 py-2 text-left text-xs">Название</th>
                    <th className="px-4 py-2 text-left text-xs">Тип</th>
                    <th className="px-4 py-2 text-right text-xs">Порог</th>
                    <th className="px-4 py-2 text-right text-xs">Вес</th>
                    <th className="px-4 py-2 text-right text-xs">Действия</th>
                  </tr>
                </thead>
                <tbody className="divide-y">
                  {inds.map((ind) => (
                    <tr key={ind.id} className="hover:bg-gray-50">
                      <td className="px-4 py-2 font-mono text-sm">{ind.code}</td>
                      <td className="px-4 py-2 text-sm">{ind.name}</td>
                      <td className="px-4 py-2">
                        <span
                          className={`px-2 py-0.5 text-xs rounded ${
                            ind.indicator_type === 'base'
                              ? 'bg-blue-100 text-blue-700'
                              : ind.indicator_type === 'extra'
                              ? 'bg-green-100 text-green-700'
                              : 'bg-red-100 text-red-700'
                          }`}
                        >
                          {ind.indicator_type}
                        </span>
                      </td>
                      <td className="px-4 py-2 text-right text-sm">{ind.base_value ?? '—'}</td>
                      <td className="px-4 py-2 text-right text-sm font-medium">
                        {ind.base_weight ?? ind.extra_weight ?? ind.penalty_weight ?? '—'}
                      </td>
                      <td className="px-4 py-2 text-right space-x-2">
                        <button
                          onClick={() => {
                            setEditingIndicator(ind);
                            setShowForm(true);
                          }}
                          className="px-2 py-1 text-xs bg-gray-100 hover:bg-gray-200 rounded"
                        >
                          Изменить
                        </button>
                        <button
                          onClick={() => handleDelete(ind.id)}
                          className="px-2 py-1 text-xs bg-red-500 text-white rounded hover:bg-red-600"
                        >
                          Удалить
                        </button>
                      </td>
                    </tr>
                  ))}
                </tbody>
              </table>
            </div>
          </div>
        ))}

      {showForm && (
        <IndicatorForm
          indicator={editingIndicator}
          categories={categories}
          onClose={() => setShowForm(false)}
          onSaved={() => {
            setShowForm(false);
            load();
          }}
        />
      )}
    </div>
  );
};

// --- Модалка для создания/редактирования ---
const IndicatorForm: React.FC<{
  indicator: KpiIndicator | null;
  categories: KpiCategory[];
  onClose: () => void;
  onSaved: () => void;
}> = ({ indicator, categories, onClose, onSaved }) => {
  const [form, setForm] = useState({
    category_code: indicator?.category_code || categories[0]?.code || '',
    code: indicator?.code || '',
    name: indicator?.name || '',
    description: indicator?.description || '',
    unit: indicator?.unit || '%',
    indicator_type: indicator?.indicator_type || ('base' as IndicatorType),
    base_value: indicator?.base_value || '',
    base_weight: indicator?.base_weight || '',
    extra_weight: indicator?.extra_weight || '',
    penalty_weight: indicator?.penalty_weight || '',
  });
  const [error, setError] = useState('');
  const [loading, setLoading] = useState(false);

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    setLoading(true);
    setError('');

    try {
      const data: any = {
        ...form,
        base_value: form.base_value !== '' ? Number(form.base_value) : null,
        base_weight: form.base_weight !== '' ? Number(form.base_weight) : null,
        extra_weight: form.extra_weight !== '' ? Number(form.extra_weight) : null,
        penalty_weight: form.penalty_weight !== '' ? Number(form.penalty_weight) : null,
      };

      if (indicator) {
        await kpiService.update(indicator.id, data);
      } else {
        await kpiService.create(data);
      }
      onSaved();
    } catch (err: any) {
      setError(err.response?.data?.message || 'Ошибка сохранения');
    } finally {
      setLoading(false);
    }
  };

  return (
    <div className="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center p-4 z-50">
      <div className="bg-white rounded-lg shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
        <div className="px-6 py-4 border-b flex items-center justify-between sticky top-0 bg-white">
          <h2 className="text-lg font-semibold">
            {indicator ? 'Редактировать показатель' : 'Новый показатель'}
          </h2>
          <button onClick={onClose} className="text-gray-400 hover:text-gray-600">✕</button>
        </div>
        <form onSubmit={handleSubmit} className="p-6 space-y-4">
          {error && <div className="p-3 bg-red-50 text-red-700 rounded text-sm">{error}</div>}

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Категория</label>
              <select
                value={form.category_code}
                onChange={(e) => setForm({ ...form, category_code: e.target.value })}
                className="w-full px-3 py-2 border rounded"
              >
                {categories.map((c) => (
                  <option key={c.code} value={c.code}>{c.code} — {c.name}</option>
                ))}
              </select>
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Код</label>
              <input
                type="text"
                required
                value={form.code}
                onChange={(e) => setForm({ ...form, code: e.target.value })}
                className="w-full px-3 py-2 border rounded"
                placeholder="ПМ1, ДПМ1, ШОЭК..."
              />
            </div>
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Название</label>
            <input
              type="text"
              required
              value={form.name}
              onChange={(e) => setForm({ ...form, name: e.target.value })}
              className="w-full px-3 py-2 border rounded"
            />
          </div>

          <div>
            <label className="block text-sm font-medium text-gray-700 mb-1">Описание</label>
            <textarea
              value={form.description}
              onChange={(e) => setForm({ ...form, description: e.target.value })}
              className="w-full px-3 py-2 border rounded"
              rows={2}
            />
          </div>

          <div className="grid grid-cols-2 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Единица измерения</label>
              <input
                type="text"
                required
                value={form.unit}
                onChange={(e) => setForm({ ...form, unit: e.target.value })}
                className="w-full px-3 py-2 border rounded"
                placeholder="%, шт, чел"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-1">Тип показателя</label>
              <select
                value={form.indicator_type}
                onChange={(e) => setForm({ ...form, indicator_type: e.target.value as IndicatorType })}
                className="w-full px-3 py-2 border rounded"
              >
                <option value="base">Базовый (base)</option>
                <option value="extra">Дополнительный (extra)</option>
                <option value="penalty">Штрафной (penalty)</option>
              </select>
            </div>
          </div>

          <div className="grid grid-cols-2 gap-4">
            {form.indicator_type === 'base' && (
              <>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Порог (base_value)</label>
                  <input
                    type="number"
                    step="0.01"
                    value={form.base_value}
                    onChange={(e) => setForm({ ...form, base_value: e.target.value })}
                    className="w-full px-3 py-2 border rounded"
                  />
                </div>
                <div>
                  <label className="block text-sm font-medium text-gray-700 mb-1">Вес (base_weight)</label>
                  <input
                    type="number"
                    value={form.base_weight}
                    onChange={(e) => setForm({ ...form, base_weight: e.target.value })}
                    className="w-full px-3 py-2 border rounded"
                  />
                </div>
              </>
            )}
            {form.indicator_type === 'extra' && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Множитель (extra_weight)</label>
                <input
                  type="number"
                  value={form.extra_weight}
                  onChange={(e) => setForm({ ...form, extra_weight: e.target.value })}
                  className="w-full px-3 py-2 border rounded"
                />
              </div>
            )}
            {form.indicator_type === 'penalty' && (
              <div>
                <label className="block text-sm font-medium text-gray-700 mb-1">Штраф (penalty_weight)</label>
                <input
                  type="number"
                  value={form.penalty_weight}
                  onChange={(e) => setForm({ ...form, penalty_weight: e.target.value })}
                  className="w-full px-3 py-2 border rounded"
                  placeholder="отрицательное число, напр. -5"
                />
              </div>
            )}
          </div>

          <div className="flex justify-end gap-2 pt-4 border-t">
            <button
              type="button"
              onClick={onClose}
              className="px-4 py-2 text-sm bg-gray-100 rounded hover:bg-gray-200"
            >
              Отмена
            </button>
            <button
              type="submit"
              disabled={loading}
              className="px-4 py-2 text-sm bg-orange-600 text-white rounded hover:bg-orange-700"
            >
              {loading ? 'Сохранение...' : 'Сохранить'}
            </button>
          </div>
        </form>
      </div>
    </div>
  );
};
